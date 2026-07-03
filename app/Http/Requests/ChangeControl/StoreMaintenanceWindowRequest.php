<?php

namespace App\Http\Requests\ChangeControl;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceWindowRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:5000'],
            'router_id' => ['nullable', 'integer', Rule::exists('routers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'site_id' => ['nullable', 'integer', Rule::exists('sites', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }
}
