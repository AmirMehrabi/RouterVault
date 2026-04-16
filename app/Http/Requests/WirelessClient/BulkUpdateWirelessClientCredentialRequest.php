<?php

namespace App\Http\Requests\WirelessClient;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkUpdateWirelessClientCredentialRequest extends FormRequest
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
            'wireless_client_ids' => ['required', 'array', 'min:1'],
            'wireless_client_ids.*' => [
                'integer',
                Rule::exists('wireless_clients', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'credential_source' => ['required', 'string', Rule::in(['manual', 'password_manager'])],
            'password_manager_credential_id' => [
                'nullable',
                Rule::exists('password_manager_credentials', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'provisioning_username' => ['nullable', 'string', 'max:255'],
            'provisioning_password' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'wireless_client_ids.required' => 'Select at least one wireless client.',
            'wireless_client_ids.*.exists' => 'One or more selected wireless clients are invalid.',
            'password_manager_credential_id.exists' => 'The selected Password Manager credential is invalid.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $credentialSource = $this->string('credential_source')->value();
            $username = trim((string) $this->input('provisioning_username', ''));
            $password = trim((string) $this->input('provisioning_password', ''));

            if ($credentialSource === 'password_manager' && ! $this->filled('password_manager_credential_id')) {
                $validator->errors()->add('password_manager_credential_id', 'Select a credential from Password Manager.');
            }

            if ($credentialSource === 'manual' && ($username === '' || $password === '')) {
                if ($username === '') {
                    $validator->errors()->add('provisioning_username', 'Enter a username for the selected wireless clients.');
                }

                if ($password === '') {
                    $validator->errors()->add('provisioning_password', 'Enter a password for the selected wireless clients.');
                }
            }
        });
    }
}
