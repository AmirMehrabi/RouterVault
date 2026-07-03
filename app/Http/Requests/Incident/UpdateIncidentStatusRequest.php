<?php

namespace App\Http\Requests\Incident;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'status' => ['required', Rule::in(['detected', 'acknowledged', 'assigned', 'investigating', 'resolved'])],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'resolution' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
