<?php

namespace App\Http\Requests\Compliance;

use App\Models\Router;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBaselineRequest extends FormRequest
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
        $router = $this->route('router');
        $routerId = $router instanceof Router ? $router->id : $router;
        $tenantId = $this->user()?->tenant_id;

        return [
            'router_backup_id' => [
                'required',
                'integer',
                Rule::exists('router_backups', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('router_id', $routerId)
                    ->whereIn('status', ['success', 'partial_success'])
                    ->whereNotNull('path')),
            ],
            'label' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
