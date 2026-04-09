<?php

namespace App\Http\Requests\PasswordManager;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePasswordManagerCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->tenant_id;
    }

    public function rules(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        $credentialId = $this->route('passwordManager')?->id;

        return [
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('password_manager_credentials')->ignore($credentialId)->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a credential name.',
            'name.unique' => 'This credential name already exists for your tenant.',
            'username.required' => 'Please provide a username.',
        ];
    }
}
