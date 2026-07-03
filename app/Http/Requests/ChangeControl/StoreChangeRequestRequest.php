<?php

namespace App\Http\Requests\ChangeControl;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChangeRequestRequest extends FormRequest
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
            'router_id' => ['required', 'integer', Rule::exists('routers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'title' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:5000'],
            'ticket_reference' => ['nullable', 'string', 'max:255'],
            'implementation_plan' => ['required', 'string', 'max:10000'],
        ];
    }
}
