<?php

namespace App\Services\RouterOs;

use App\Models\User;
use App\Models\WirelessClient;
use App\Models\WirelessClientManagementLog;
use App\Models\WirelessClientManagementSnapshot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RouterOS\Client;
use RouterOS\Query;
use Throwable;

class WirelessClientManagementService
{
    public function __construct(
        protected WirelessClientCommandRegistry $commandRegistry
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        return $this->commandRegistry->definitions();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function groupedDefinitions(): array
    {
        return $this->commandRegistry->groupedDefinitions();
    }

    public function executeAction(WirelessClient $wirelessClient, string $actionKey, array $payload = [], ?User $actor = null): WirelessClientManagementLog
    {
        $definition = $this->commandRegistry->definition($actionKey);

        if ($definition === []) {
            throw new \InvalidArgumentException('Unknown wireless client management action.');
        }

        $log = WirelessClientManagementLog::query()->create([
            'tenant_id' => $wirelessClient->tenant_id,
            'wireless_client_id' => $wirelessClient->id,
            'user_id' => $actor?->id,
            'action_key' => $actionKey,
            'action_label' => $definition['label'],
            'status' => 'running',
            'target_host' => $wirelessClient->resolvedManagementHost(),
            'request_payload' => $payload,
            'started_at' => now(),
        ]);

        try {
            $this->guardManageable($wirelessClient);

            $client = $this->makeClient($wirelessClient);
            $result = match ($actionKey) {
                'discovery' => $this->runDiscovery($wirelessClient, $client),
                'refresh_signal' => $this->runSignalRefresh($wirelessClient, $client),
                'refresh_dhcp_lease' => $this->runDhcpLeaseRefresh($wirelessClient, $client),
                'set_identity' => $this->setIdentity($wirelessClient, $client, $payload),
                'set_dns' => $this->setDns($wirelessClient, $client, $payload),
                'set_ntp' => $this->setNtp($wirelessClient, $client, $payload),
                'set_timezone' => $this->setTimezone($wirelessClient, $client, $payload),
                'set_snmp' => $this->setSnmp($wirelessClient, $client, $payload),
                'set_password' => $this->setPassword($wirelessClient, $client, $payload),
                'reboot' => $this->reboot($wirelessClient, $client),
                default => throw new \InvalidArgumentException('Unsupported wireless client management action.'),
            };

            DB::transaction(function () use ($wirelessClient, $log, $actionKey, $result): void {
                if (($result['snapshot_type'] ?? null) !== null) {
                    WirelessClientManagementSnapshot::query()->create([
                        'tenant_id' => $wirelessClient->tenant_id,
                        'wireless_client_id' => $wirelessClient->id,
                        'action_key' => $actionKey,
                        'snapshot_type' => $result['snapshot_type'],
                        'payload' => $result['snapshot_payload'] ?? [],
                        'collected_at' => now(),
                    ]);
                }

                if (($result['client_updates'] ?? []) !== []) {
                    $wirelessClient->forceFill($result['client_updates'])->save();
                }

                $wirelessClient->forceFill([
                    'last_management_status' => 'success',
                    'last_management_message' => $result['summary'] ?? 'Command executed successfully.',
                    'last_management_ran_at' => now(),
                ])->save();

                $log->forceFill([
                    'status' => 'success',
                    'command_batch' => $result['command_batch'] ?? null,
                    'response_payload' => $result['response_payload'] ?? null,
                    'summary' => $result['summary'] ?? null,
                    'finished_at' => now(),
                ])->save();
            });
        } catch (Throwable $throwable) {
            $wirelessClient->forceFill([
                'last_management_status' => 'failed',
                'last_management_message' => $throwable->getMessage(),
                'last_management_ran_at' => now(),
            ])->save();

            $log->forceFill([
                'status' => 'failed',
                'error_message' => $throwable->getMessage(),
                'summary' => 'Command failed.',
                'finished_at' => now(),
            ])->save();
        }

        return $log->fresh(['user:id,name']);
    }

    public function refreshManageableClients(?int $limit = null): int
    {
        $query = WirelessClient::query()
            ->withoutGlobalScopes()
            ->whereNotNull('tenant_id')
            ->where(function ($builder) {
                $builder->whereNotNull('management_ip_address')
                    ->orWhereNotNull('last_ip_address');
            })
            ->where(function ($builder) {
                $builder->whereNotNull('password_manager_credential_id')
                    ->orWhere(function ($credentialQuery) {
                        $credentialQuery->whereNotNull('provisioning_username')
                            ->whereNotNull('provisioning_password');
                    });
            })
            ->with(['accessPoint:id,vendor', 'router:id,vendor']);

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $processed = 0;

        foreach ($query->get() as $wirelessClient) {
            if (! $wirelessClient->isMikrotikManageable()) {
                continue;
            }

            $this->executeAction($wirelessClient, 'discovery');
            $processed++;
        }

        return $processed;
    }

    protected function guardManageable(WirelessClient $wirelessClient): void
    {
        if (! $wirelessClient->isMikrotikManageable()) {
            throw new \RuntimeException('Administrative actions are only available for MikroTik wireless clients.');
        }

        if (! $wirelessClient->resolvedManagementHost()) {
            throw new \RuntimeException('No management IP address is available for this wireless client.');
        }

        if (! $wirelessClient->resolvedManagementUsername() || ! $wirelessClient->resolvedManagementPassword()) {
            throw new \RuntimeException('No management credential is available for this wireless client.');
        }
    }

    protected function makeClient(WirelessClient $wirelessClient): Client
    {
        return new Client([
            'host' => $wirelessClient->resolvedManagementHost(),
            'user' => $wirelessClient->resolvedManagementUsername(),
            'pass' => $wirelessClient->resolvedManagementPassword(),
            'port' => $wirelessClient->resolvedManagementPort(),
            'timeout' => 10,
            'attempts' => 1,
            'delay' => 1,
            'socket_timeout' => 10,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function runDiscovery(WirelessClient $wirelessClient, Client $client): array
    {
        $identity = $this->firstRow($this->query($client, '/system/identity/print'));
        $pppoeClients = $this->query($client, '/interface/pppoe-client/print');
        $wirelessInterfaces = $this->queryFirstSuccessful($client, [
            '/interface/wireless/print',
            '/interface/wifi/print',
        ]);
        $resource = $this->firstRow($this->query($client, '/system/resource/print'));
        $clock = $this->firstRow($this->query($client, '/system/clock/print'));
        $dns = $this->firstRow($this->query($client, '/ip/dns/print'));
        $ntp = $this->firstRow($this->query($client, '/system/ntp/client/print'));
        $snmp = $this->firstRow($this->query($client, '/snmp/print'));
        $snmpCommunities = $this->query($client, '/snmp/community/print');
        $registrationTable = $this->queryFirstSuccessful($client, [
            '/interface/wireless/registration-table/print',
            '/interface/wifi/registration-table/print',
        ]);
        $dhcpLease = $this->findDhcpLease($client, $wirelessClient);

        $primaryPppoe = collect($pppoeClients)->first(fn (array $item): bool => ! empty($item['user']) || ! empty($item['name']));
        $primaryWireless = collect($wirelessInterfaces)->first(function (array $item): bool {
            return ($item['disabled'] ?? 'false') !== 'true';
        }) ?? ($wirelessInterfaces[0] ?? []);
        $primaryRegistration = $this->resolveRegistrationEntry($registrationTable, $wirelessClient);

        $snapshot = [
            'identity' => $identity,
            'pppoe_clients' => $pppoeClients,
            'wireless_interfaces' => $wirelessInterfaces,
            'resource' => $resource,
            'clock' => $clock,
            'dns' => $dns,
            'ntp_client' => $ntp,
            'snmp' => $snmp,
            'snmp_communities' => $snmpCommunities,
            'registration_table' => $registrationTable,
            'dhcp_lease' => $dhcpLease,
        ];

        return [
            'summary' => 'Discovery data refreshed successfully.',
            'command_batch' => [
                '/system/identity/print',
                '/interface/pppoe-client/print',
                '/interface/wireless/print|/interface/wifi/print',
                '/system/resource/print',
                '/system/clock/print',
                '/ip/dns/print',
                '/system/ntp/client/print',
                '/snmp/print',
                '/snmp/community/print',
                '/interface/wireless/registration-table/print',
                '/ip/dhcp-server/lease/print',
            ],
            'response_payload' => $snapshot,
            'snapshot_type' => 'discovery',
            'snapshot_payload' => $snapshot,
            'client_updates' => [
                'device_identity' => Arr::get($identity, 'name'),
                'pppoe_username' => Arr::get($primaryPppoe, 'user') ?? Arr::get($primaryPppoe, 'name'),
                'device_mac_address' => Arr::get($primaryWireless, 'mac-address') ?? Arr::get($primaryWireless, 'mac_address'),
                'device_version' => Arr::get($resource, 'version'),
                'device_uptime' => Arr::get($resource, 'uptime'),
                'management_ip_address' => $wirelessClient->management_ip_address ?: ($wirelessClient->last_ip_address ?: Arr::get($dhcpLease, 'address')),
                'last_ip_address' => Arr::get($dhcpLease, 'address', $wirelessClient->last_ip_address),
                'signal_strength' => $this->toInt(Arr::get($primaryRegistration, 'signal-strength') ?? Arr::get($primaryRegistration, 'signal')) ?? $wirelessClient->signal_strength,
                'signal_to_noise' => $this->toInt(Arr::get($primaryRegistration, 'signal-to-noise')) ?? $wirelessClient->signal_to_noise,
                'tx_rate' => Arr::get($primaryRegistration, 'tx-rate') ?? Arr::get($primaryRegistration, 'tx_rate') ?? $wirelessClient->tx_rate,
                'rx_rate' => Arr::get($primaryRegistration, 'rx-rate') ?? Arr::get($primaryRegistration, 'rx_rate') ?? $wirelessClient->rx_rate,
                'last_snapshot' => $snapshot,
                'last_discovered_at' => now(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function runSignalRefresh(WirelessClient $wirelessClient, Client $client): array
    {
        $registrationTable = $this->queryFirstSuccessful($client, [
            '/interface/wireless/registration-table/print',
            '/interface/wifi/registration-table/print',
        ]);
        $entry = $this->resolveRegistrationEntry($registrationTable, $wirelessClient);

        return [
            'summary' => $entry === [] ? 'Registration table did not return a matching entry.' : 'Signal metrics refreshed successfully.',
            'command_batch' => ['/interface/wireless/registration-table/print'],
            'response_payload' => ['registration_entry' => $entry, 'registration_table' => $registrationTable],
            'snapshot_type' => 'signal',
            'snapshot_payload' => ['registration_entry' => $entry, 'registration_table' => $registrationTable],
            'client_updates' => [
                'signal_strength' => $this->toInt(Arr::get($entry, 'signal-strength') ?? Arr::get($entry, 'signal')) ?? $wirelessClient->signal_strength,
                'signal_to_noise' => $this->toInt(Arr::get($entry, 'signal-to-noise')) ?? $wirelessClient->signal_to_noise,
                'tx_rate' => Arr::get($entry, 'tx-rate') ?? Arr::get($entry, 'tx_rate') ?? $wirelessClient->tx_rate,
                'rx_rate' => Arr::get($entry, 'rx-rate') ?? Arr::get($entry, 'rx_rate') ?? $wirelessClient->rx_rate,
                'tx_ccq' => $this->toInt(Arr::get($entry, 'tx-ccq')) ?? $wirelessClient->tx_ccq,
                'rx_ccq' => $this->toInt(Arr::get($entry, 'rx-ccq')) ?? $wirelessClient->rx_ccq,
                'last_snapshot' => ['signal' => $entry],
                'last_discovered_at' => now(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function runDhcpLeaseRefresh(WirelessClient $wirelessClient, Client $client): array
    {
        $lease = $this->findDhcpLease($client, $wirelessClient);

        return [
            'summary' => $lease === [] ? 'No DHCP lease matched this wireless client.' : 'DHCP lease refreshed successfully.',
            'command_batch' => ['/ip/dhcp-server/lease/print'],
            'response_payload' => ['lease' => $lease],
            'snapshot_type' => 'dhcp_lease',
            'snapshot_payload' => ['lease' => $lease],
            'client_updates' => [
                'last_ip_address' => Arr::get($lease, 'address', $wirelessClient->last_ip_address),
                'management_ip_address' => $wirelessClient->management_ip_address ?: Arr::get($lease, 'address'),
                'last_snapshot' => ['dhcp_lease' => $lease],
                'last_discovered_at' => now(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function setIdentity(WirelessClient $wirelessClient, Client $client, array $payload): array
    {
        $query = (new Query('/system/identity/set'))
            ->equal('name', (string) $payload['identity']);

        $client->query($query)->read();
        $identity = $this->firstRow($this->query($client, '/system/identity/print'));

        return [
            'summary' => 'Identity updated successfully.',
            'command_batch' => [
                '/system/identity set name="'.$payload['identity'].'"',
            ],
            'response_payload' => ['identity' => $identity],
            'snapshot_type' => 'configuration',
            'snapshot_payload' => ['identity' => $identity],
            'client_updates' => [
                'device_identity' => Arr::get($identity, 'name', (string) $payload['identity']),
                'last_discovered_at' => now(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function setDns(WirelessClient $wirelessClient, Client $client, array $payload): array
    {
        $servers = $this->csv($payload['dns_servers'] ?? '');
        $query = (new Query('/ip/dns/set'))
            ->equal('servers', implode(',', $servers))
            ->equal('allow-remote-requests', ! empty($payload['allow_remote_requests']) ? 'yes' : 'no');

        $client->query($query)->read();
        $dns = $this->firstRow($this->query($client, '/ip/dns/print'));

        return [
            'summary' => 'DNS settings updated successfully.',
            'command_batch' => [
                '/ip/dns set servers='.implode(',', $servers).' allow-remote-requests='.(! empty($payload['allow_remote_requests']) ? 'yes' : 'no'),
            ],
            'response_payload' => ['dns' => $dns],
            'snapshot_type' => 'configuration',
            'snapshot_payload' => ['dns' => $dns],
            'client_updates' => [
                'last_discovered_at' => now(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function setNtp(WirelessClient $wirelessClient, Client $client, array $payload): array
    {
        $servers = $this->csv($payload['ntp_servers'] ?? '');
        $enabled = ! empty($payload['ntp_enabled']) ? 'yes' : 'no';

        $query = (new Query('/system/ntp/client/set'))
            ->equal('enabled', $enabled);

        if ($servers !== []) {
            $query->equal('servers', implode(',', $servers));
        }

        $client->query($query)->read();
        $ntp = $this->firstRow($this->query($client, '/system/ntp/client/print'));

        return [
            'summary' => 'NTP settings updated successfully.',
            'command_batch' => [
                '/system/ntp/client set enabled='.$enabled.($servers !== [] ? ' servers='.implode(',', $servers) : ''),
            ],
            'response_payload' => ['ntp_client' => $ntp],
            'snapshot_type' => 'configuration',
            'snapshot_payload' => ['ntp_client' => $ntp],
            'client_updates' => [
                'last_discovered_at' => now(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function setTimezone(WirelessClient $wirelessClient, Client $client, array $payload): array
    {
        $query = (new Query('/system/clock/set'))
            ->equal('time-zone-name', (string) $payload['time_zone_name']);

        $client->query($query)->read();
        $clock = $this->firstRow($this->query($client, '/system/clock/print'));

        return [
            'summary' => 'Timezone updated successfully.',
            'command_batch' => [
                '/system/clock set time-zone-name="'.$payload['time_zone_name'].'"',
            ],
            'response_payload' => ['clock' => $clock],
            'snapshot_type' => 'configuration',
            'snapshot_payload' => ['clock' => $clock],
            'client_updates' => [
                'last_discovered_at' => now(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function setSnmp(WirelessClient $wirelessClient, Client $client, array $payload): array
    {
        $enabled = ! empty($payload['snmp_enabled']) ? 'yes' : 'no';
        $communityName = (string) ($payload['snmp_community'] ?? '');
        $addresses = implode(',', $this->csv($payload['snmp_addresses'] ?? ''));

        $client->query(
            (new Query('/snmp/set'))->equal('enabled', $enabled)
        )->read();

        if ($enabled === 'yes' && $communityName !== '') {
            $existing = $this->firstRow(
                $this->query($client, '/snmp/community/print', function (Query $query) use ($communityName): void {
                    $query->where('name', $communityName);
                })
            );

            if ($existing !== []) {
                $communityQuery = (new Query('/snmp/community/set'))
                    ->equal('.id', (string) $existing['.id'])
                    ->equal('name', $communityName)
                    ->equal('addresses', $addresses);
            } else {
                $communityQuery = (new Query('/snmp/community/add'))
                    ->equal('name', $communityName)
                    ->equal('addresses', $addresses);
            }

            $client->query($communityQuery)->read();
        }

        $snmp = $this->firstRow($this->query($client, '/snmp/print'));
        $communities = $this->query($client, '/snmp/community/print');

        return [
            'summary' => 'SNMP settings updated successfully.',
            'command_batch' => [
                '/snmp set enabled='.$enabled,
                $enabled === 'yes' && $communityName !== '' ? '/snmp community '.($addresses !== '' ? 'set/add ' : 'set/add ').'name='.$communityName : null,
            ],
            'response_payload' => ['snmp' => $snmp, 'communities' => $communities],
            'snapshot_type' => 'configuration',
            'snapshot_payload' => ['snmp' => $snmp, 'communities' => $communities],
            'client_updates' => [
                'last_discovered_at' => now(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function setPassword(WirelessClient $wirelessClient, Client $client, array $payload): array
    {
        $users = $this->query($client, '/user/print', function (Query $query): void {
            $query->where('name', 'admin');
        });
        $admin = $this->firstRow($users);

        if ($admin === []) {
            throw new \RuntimeException('Could not find the admin user on the wireless client.');
        }

        $client->query(
            (new Query('/user/set'))
                ->equal('.id', (string) $admin['.id'])
                ->equal('password', (string) $payload['password'])
        )->read();

        $summary = 'Admin password changed successfully.';
        if ($wirelessClient->password_manager_credential_id) {
            $summary .= ' Update the shared password manager record if this radio no longer matches it.';
        }

        return [
            'summary' => $summary,
            'command_batch' => ['/user set admin password=******'],
            'response_payload' => ['password_changed' => true],
            'snapshot_type' => 'action_result',
            'snapshot_payload' => ['password_changed' => true],
            'client_updates' => [
                'provisioning_password' => $wirelessClient->password_manager_credential_id ? $wirelessClient->provisioning_password : (string) $payload['password'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function reboot(WirelessClient $wirelessClient, Client $client): array
    {
        $client->query(new Query('/system/reboot'))->read();

        return [
            'summary' => 'Reboot command sent to the radio.',
            'command_batch' => ['/system reboot'],
            'response_payload' => ['reboot' => 'requested'],
            'snapshot_type' => 'action_result',
            'snapshot_payload' => ['reboot' => 'requested'],
            'client_updates' => [],
        ];
    }

    /**
     * @param  callable(Query):void|null  $tap
     * @return array<int, array<string, mixed>>
     */
    protected function query(Client $client, string $endpoint, ?callable $tap = null): array
    {
        $query = new Query($endpoint);

        if ($tap !== null) {
            $tap($query);
        }

        $response = $client->query($query)->read();

        return is_array($response) ? $response : [];
    }

    /**
     * @param  array<int, string>  $endpoints
     * @return array<int, array<string, mixed>>
     */
    protected function queryFirstSuccessful(Client $client, array $endpoints): array
    {
        foreach ($endpoints as $endpoint) {
            try {
                return $this->query($client, $endpoint);
            } catch (Throwable) {
            }
        }

        return [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    protected function resolveRegistrationEntry(array $rows, WirelessClient $wirelessClient): array
    {
        $normalizedMac = Str::upper((string) $wirelessClient->mac_address);

        return collect($rows)
            ->first(function (array $row) use ($normalizedMac): bool {
                $rowMac = Str::upper((string) (Arr::get($row, 'mac-address') ?? Arr::get($row, 'mac_address')));

                return $normalizedMac !== '' && $rowMac === $normalizedMac;
            })
            ?? ($rows[0] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    protected function findDhcpLease(Client $client, WirelessClient $wirelessClient): array
    {
        $leases = $this->query($client, '/ip/dhcp-server/lease/print');
        $normalizedMac = Str::upper((string) $wirelessClient->mac_address);

        return collect($leases)
            ->first(function (array $lease) use ($wirelessClient, $normalizedMac): bool {
                $leaseMac = Str::upper((string) (Arr::get($lease, 'mac-address') ?? Arr::get($lease, 'active-mac-address')));
                $leaseAddress = (string) (Arr::get($lease, 'address') ?? Arr::get($lease, 'active-address'));

                if ($normalizedMac !== '' && $leaseMac === $normalizedMac) {
                    return true;
                }

                return $wirelessClient->last_ip_address !== null && $leaseAddress === $wirelessClient->last_ip_address;
            })
            ?? [];
    }

    /**
     * @param  mixed  $value
     */
    protected function toInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        preg_match('/-?\d+/', (string) $value, $matches);

        return isset($matches[0]) ? (int) $matches[0] : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $response
     * @return array<string, mixed>
     */
    protected function firstRow(array $response): array
    {
        return $response[0] ?? [];
    }

    /**
     * @param  string|array<int, string>  $value
     * @return array<int, string>
     */
    protected function csv(string|array $value): array
    {
        $items = is_array($value) ? $value : explode(',', $value);

        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn (string $item) => $item !== '')
            ->values()
            ->all();
    }
}
