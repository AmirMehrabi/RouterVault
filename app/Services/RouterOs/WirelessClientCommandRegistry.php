<?php

namespace App\Services\RouterOs;

class WirelessClientCommandRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        return [
            'discovery' => [
                'key' => 'discovery',
                'label' => 'Run Discovery',
                'description' => 'Collect identity, PPPoE, version, uptime, wireless data, signal, and DHCP lease details.',
                'group' => 'Read',
                'danger_level' => 'safe',
                'requires_confirmation' => false,
            ],
            'refresh_signal' => [
                'key' => 'refresh_signal',
                'label' => 'Refresh Signal',
                'description' => 'Read the registration table and store the latest radio signal metrics.',
                'group' => 'Read',
                'danger_level' => 'safe',
                'requires_confirmation' => false,
            ],
            'refresh_dhcp_lease' => [
                'key' => 'refresh_dhcp_lease',
                'label' => 'Refresh DHCP Lease',
                'description' => 'Look up the matching DHCP lease and store the latest customer IP address.',
                'group' => 'Read',
                'danger_level' => 'safe',
                'requires_confirmation' => false,
            ],
            'set_identity' => [
                'key' => 'set_identity',
                'label' => 'Set Identity',
                'description' => 'Update the MikroTik identity so the radio is easier to find in operations.',
                'group' => 'Configure',
                'danger_level' => 'safe',
                'requires_confirmation' => false,
            ],
            'set_dns' => [
                'key' => 'set_dns',
                'label' => 'Set DNS',
                'description' => 'Configure DNS servers and remote request handling on the radio.',
                'group' => 'Configure',
                'danger_level' => 'safe',
                'requires_confirmation' => false,
            ],
            'set_ntp' => [
                'key' => 'set_ntp',
                'label' => 'Set NTP',
                'description' => 'Enable or update NTP servers to keep the radio clock synchronized.',
                'group' => 'Configure',
                'danger_level' => 'safe',
                'requires_confirmation' => false,
            ],
            'set_timezone' => [
                'key' => 'set_timezone',
                'label' => 'Set Timezone',
                'description' => 'Set the RouterOS timezone name used by the radio.',
                'group' => 'Configure',
                'danger_level' => 'safe',
                'requires_confirmation' => false,
            ],
            'set_snmp' => [
                'key' => 'set_snmp',
                'label' => 'Configure SNMP',
                'description' => 'Enable SNMP and create or update the named community definition.',
                'group' => 'Configure',
                'danger_level' => 'warning',
                'requires_confirmation' => false,
            ],
            'set_password' => [
                'key' => 'set_password',
                'label' => 'Change Password',
                'description' => 'Change the admin password on the radio.',
                'group' => 'Security',
                'danger_level' => 'warning',
                'requires_confirmation' => true,
            ],
            'reboot' => [
                'key' => 'reboot',
                'label' => 'Reboot Radio',
                'description' => 'Restart the radio immediately.',
                'group' => 'Security',
                'danger_level' => 'danger',
                'requires_confirmation' => true,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function groupedDefinitions(): array
    {
        $groups = [];

        foreach ($this->definitions() as $definition) {
            $groups[$definition['group']][] = $definition;
        }

        return collect($groups)
            ->map(fn (array $actions, string $label) => [
                'label' => $label,
                'actions' => array_values($actions),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(string $actionKey): array
    {
        return $this->definitions()[$actionKey] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->definitions());
    }
}
