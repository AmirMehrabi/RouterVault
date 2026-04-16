<?php

namespace App\Http\Requests\WirelessClient;

use App\Models\WirelessClient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateWirelessClientCredentialRequest extends FormRequest
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
            'credential_source' => ['required', 'string', Rule::in(['manual', 'password_manager'])],
            'redirect_route' => ['nullable', 'string', Rule::in(['index', 'show'])],
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
            'credential_source.required' => 'Choose how you want to provision this wireless client.',
            'password_manager_credential_id.exists' => 'The selected Password Manager credential is invalid.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var WirelessClient|null $wirelessClient */
            $wirelessClient = $this->route('wirelessClient');
            $credentialSource = $this->string('credential_source')->value();
            $username = trim((string) $this->input('provisioning_username', ''));
            $password = trim((string) $this->input('provisioning_password', ''));

            if ($credentialSource === 'password_manager' && ! $this->filled('password_manager_credential_id')) {
                $validator->errors()->add('password_manager_credential_id', 'Select a credential from Password Manager.');
            }

            if ($credentialSource !== 'manual') {
                return;
            }

            $existingUsername = trim((string) ($wirelessClient?->provisioning_username ?? ''));
            $existingPassword = trim((string) ($wirelessClient?->provisioning_password ?? ''));

            if ($username === '' && $existingUsername === '') {
                $validator->errors()->add('provisioning_username', 'Enter a username for manual provisioning.');
            }

            if ($password === '' && $existingPassword === '') {
                $validator->errors()->add('provisioning_password', 'Enter a password for manual provisioning.');
            }
        });
    }
}
