<?php

namespace App\Http\Requests\Backup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompareBackupsRequest extends FormRequest
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
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;
        $routerId = $this->integer('router_id');

        return [
            'router_id' => ['nullable', Rule::exists('routers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'old_backup_id' => [
                'nullable',
                'required_with:new_backup_id',
                'different:new_backup_id',
                Rule::exists('router_backups', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('router_id', $routerId)
                    ->whereIn('status', ['success', 'partial_success'])
                    ->whereNotNull('path')),
            ],
            'new_backup_id' => [
                'nullable',
                'required_with:old_backup_id',
                'different:old_backup_id',
                Rule::exists('router_backups', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('router_id', $routerId)
                    ->whereIn('status', ['success', 'partial_success'])
                    ->whereNotNull('path')),
            ],
        ];
    }
}
