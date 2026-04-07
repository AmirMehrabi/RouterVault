<?php

namespace App\Http\Requests\AccessPoint;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccessPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->tenant_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = auth()->user()?->tenant_id;

        return [
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'router_id' => ['nullable', Rule::exists('routers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'site_id' => ['nullable', Rule::exists('sites', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'board_name' => ['nullable', 'string', 'max:255'],
            'vendor' => ['required', 'string', 'in:Mikrotik,Ubiquiti,Cambium,TP-Link,Cisco,Other'],
            'ip_address' => ['nullable', 'ip', Rule::unique('access_points')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'mac_address' => ['nullable', 'mac_address', Rule::unique('access_points')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'ssid' => ['nullable', 'string', 'max:255'],
            'band' => ['required', 'string', 'in:2.4GHz,5GHz,6GHz,dual'],
            'channel' => ['nullable', 'string', 'max:50'],
            'frequency' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'tx_power' => ['nullable', 'integer', 'min:0', 'max:60'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:online,offline,maintenance'],
            'firmware_version' => ['nullable', 'string', 'max:255'],
            'architecture_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'uptime' => ['nullable', 'string', 'max:255'],
            'cpu_usage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'cpu_count' => ['nullable', 'integer', 'min:1', 'max:128'],
            'cpu_frequency' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'memory_usage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'total_memory' => ['nullable', 'integer', 'min:0'],
            'free_memory' => ['nullable', 'integer', 'min:0'],
            'total_hdd_space' => ['nullable', 'integer', 'min:0'],
            'free_hdd_space' => ['nullable', 'integer', 'min:0'],
            'connected_clients_count' => ['nullable', 'integer', 'min:0'],
            'signal_quality' => ['nullable', 'integer', 'min:0', 'max:100'],
            'noise_floor' => ['nullable', 'integer', 'min:-150', 'max:0'],
            'channel_utilization' => ['nullable', 'integer', 'min:0', 'max:100'],
            'enable_monitoring' => ['nullable', 'boolean'],
            'enable_provisioning' => ['nullable', 'boolean'],
            'last_seen_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The access point name is required.',
            'vendor.required' => 'The vendor field is required.',
            'vendor.in' => 'The vendor must be one of: Mikrotik, Ubiquiti, Cambium, TP-Link, Cisco, Other.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'ip_address.unique' => 'An access point with this IP address already exists.',
            'mac_address.mac_address' => 'Please enter a valid MAC address.',
            'mac_address.unique' => 'An access point with this MAC address already exists.',
        ];
    }
}
