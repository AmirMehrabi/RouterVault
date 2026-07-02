<?php

namespace App\Http\Requests\BackupSchedule;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterScheduleBackupsRequest extends FormRequest
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
        return [
            'router_id' => ['nullable', 'integer', Rule::exists('routers', 'id')->where(fn ($query) => $query->where('tenant_id', $this->user()?->tenant_id))],
            'status' => ['nullable', 'string', 'in:pending,running,success,failed'],
            'changed' => ['nullable', 'boolean'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'backups_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
