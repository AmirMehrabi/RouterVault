<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->tenant_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                Rule::exists('plans', 'id')->where(fn ($query) => $query
                    ->where('is_saas_plan', true)
                    ->where('is_extra_router', false)
                    ->where('status', 'active')),
            ],
        ];
    }

    public function messages(): array
    {
        return ['plan_id.exists' => 'The selected plan is no longer available.'];
    }
}
