<?php

namespace App\Http\Requests\Router;

use App\Models\Router;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = auth()->user()?->tenant_id ?? tenant()?->id;
        $router = $this->route('router');
        $routerId = $router instanceof Router ? $router->id : $router;

        return [
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'vendor' => ['required', 'string', 'in:Mikrotik,Cisco,Juniper,Huawei'],
            'ip_address' => ['required', 'ip', 'unique:routers,ip_address,'.$routerId],
            'api_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'use_ssl' => ['nullable', 'boolean'],
            'legacy_login' => ['nullable', 'boolean'],
            'api_username' => ['nullable', 'string', 'max:255'],
            'api_password' => ['nullable', 'string', 'max:255'],
            'ssh_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'ssh_auth_method' => ['nullable', 'string', Rule::in(['private_key', 'password'])],
            'ssh_private_key' => ['nullable', 'string'],
            'ssh_timeout' => ['nullable', 'integer', 'min:1', 'max:300'],
            'location' => ['nullable', 'string', 'max:255'],
            'site' => ['nullable', 'string', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:300'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'online', 'offline'])],
            'enable_monitoring' => ['nullable', 'boolean'],
            'enable_provisioning' => ['nullable', 'boolean'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'credential_source' => ['nullable', 'string', Rule::in(['manual', 'password_manager'])],
            'password_manager_credential_id' => ['nullable', Rule::exists('password_manager_credentials', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The router name is required.',
            'vendor.required' => 'The vendor field is required.',
            'vendor.in' => 'The vendor must be one of: Mikrotik, Cisco, Juniper, Huawei.',
            'ip_address.required' => 'The IP address is required.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'ip_address.unique' => 'A router with this IP address already exists.',
            'password_manager_credential_id.exists' => 'The selected Password Manager credential is invalid.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $credentialSource = $this->string('credential_source')->value();
            $username = trim((string) $this->input('api_username', ''));
            $password = trim((string) $this->input('api_password', ''));
            $router = $this->route('router');

            if ($credentialSource === 'password_manager' && ! $this->filled('password_manager_credential_id')) {
                $validator->errors()->add('password_manager_credential_id', 'Select a credential from Password Manager.');
            }

            if ($credentialSource !== 'password_manager' && $username === '' && $password !== '' && blank($router?->api_username)) {
                $validator->errors()->add('api_username', 'Enter a username for manual credentials.');
            }

            if ($credentialSource !== 'password_manager' && $username !== '' && $password === '' && blank($router?->api_password)) {
                $validator->errors()->add('api_password', 'Enter a password for manual credentials.');
            }
        });
    }
}
