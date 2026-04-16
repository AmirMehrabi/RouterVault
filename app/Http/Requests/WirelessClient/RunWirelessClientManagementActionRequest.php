<?php

namespace App\Http\Requests\WirelessClient;

use App\Services\RouterOs\WirelessClientCommandRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RunWirelessClientManagementActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->tenant_id;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_remote_requests' => $this->boolean('allow_remote_requests'),
            'ntp_enabled' => $this->boolean('ntp_enabled', true),
            'snmp_enabled' => $this->boolean('snmp_enabled', true),
            'confirm_action' => $this->boolean('confirm_action'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $action = (string) $this->route('action');
        $commandRegistry = app(WirelessClientCommandRegistry::class);

        $rules = [
            'action' => ['nullable', Rule::in($commandRegistry->keys())],
        ];

        return $rules + match ($action) {
            'set_identity' => [
                'identity' => ['required', 'string', 'max:120'],
            ],
            'set_dns' => [
                'dns_servers' => ['required', 'string', 'max:255'],
                'allow_remote_requests' => ['required', 'boolean'],
            ],
            'set_ntp' => [
                'ntp_enabled' => ['required', 'boolean'],
                'ntp_servers' => ['nullable', 'string', 'max:255'],
            ],
            'set_timezone' => [
                'time_zone_name' => ['required', 'string', 'max:120'],
            ],
            'set_snmp' => [
                'snmp_enabled' => ['required', 'boolean'],
                'snmp_community' => ['nullable', 'string', 'max:120'],
                'snmp_addresses' => ['nullable', 'string', 'max:255'],
            ],
            'set_password' => [
                'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
                'confirm_action' => ['accepted'],
            ],
            'reboot' => [
                'confirm_action' => ['accepted'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'identity.required' => 'Enter the new radio identity.',
            'dns_servers.required' => 'Enter at least one DNS server.',
            'time_zone_name.required' => 'Enter the timezone name that should be applied.',
            'password.confirmed' => 'The password confirmation does not match.',
            'confirm_action.accepted' => 'Please confirm this action before running it.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $action = (string) $this->route('action');

            if ($action === 'set_dns') {
                foreach ($this->csv('dns_servers') as $server) {
                    if (! filter_var($server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        $validator->errors()->add('dns_servers', 'DNS servers must be comma-separated IPv4 addresses.');
                        break;
                    }
                }
            }

            if ($action === 'set_ntp' && $this->boolean('ntp_enabled') && $this->csv('ntp_servers') === []) {
                $validator->errors()->add('ntp_servers', 'Enter at least one NTP server when NTP is enabled.');
            }

            if ($action === 'set_snmp' && $this->boolean('snmp_enabled')) {
                if (trim((string) $this->input('snmp_community', '')) === '') {
                    $validator->errors()->add('snmp_community', 'Enter the SNMP community name.');
                }

                if ($this->csv('snmp_addresses') === []) {
                    $validator->errors()->add('snmp_addresses', 'Enter at least one allowed SNMP address or CIDR.');
                }

                foreach ($this->csv('snmp_addresses') as $address) {
                    if (! preg_match('/^\d{1,3}(?:\.\d{1,3}){3}(?:\/\d{1,2})?$/', $address)) {
                        $validator->errors()->add('snmp_addresses', 'SNMP addresses must be comma-separated IPv4 addresses or CIDR ranges.');
                        break;
                    }
                }
            }
        });
    }

    /**
     * @return array<int, string>
     */
    protected function csv(string $key): array
    {
        return collect(explode(',', (string) $this->input($key, '')))
            ->map(fn (string $value) => trim($value))
            ->filter(fn (string $value) => $value !== '')
            ->values()
            ->all();
    }
}
