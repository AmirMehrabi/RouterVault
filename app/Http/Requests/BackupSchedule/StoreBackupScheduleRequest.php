<?php

namespace App\Http\Requests\BackupSchedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBackupScheduleRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tenantId = auth()->user()?->tenant_id ?? tenant()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'is_enabled' => ['nullable', 'boolean'],
            'interval_value' => ['required', 'integer', 'min:1', 'max:1000'],
            'interval_unit' => ['required', 'string', Rule::in(['minutes', 'hours', 'days', 'weeks'])],
            'timezone' => ['required', 'timezone'],
            'next_run_at' => ['nullable', 'date'],
            'retention_count' => ['required', 'integer', 'min:1', 'max:3650'],
            'router_ids' => ['required', 'array', 'min:1'],
            'router_ids.*' => ['integer', Rule::exists('routers', 'id')->where(fn ($query) => $query
                ->where('tenant_id', $tenantId)
                ->where(fn ($backupQuery) => $backupQuery->where('backup_rsc_enabled', true)->orWhere('backup_binary_enabled', true)))],
        ];
    }
}
