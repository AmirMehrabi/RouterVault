<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tenant_id !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'ip_address' => [
                'required',
                'ip',
                Rule::unique('routers', 'ip_address')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'api_username' => ['required', 'string', 'max:255'],
            'api_password' => ['required', 'string', 'max:255'],
            'ssh_auth_method' => ['nullable', Rule::in(['private_key', 'password'])],
            'ssh_private_key' => ['nullable', 'string'],
            'ssh_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ip_address.ip' => 'Please enter a valid IP address.',
            'ip_address.unique' => 'A router with this IP address already exists in your account.',
        ];
    }
}
