<?php

namespace App\Http\Requests\ChangeControl;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChangeRequestStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $status = $this->string('status')->toString();

        if (in_array($status, ['approved', 'cancelled'], true)) {
            return in_array($this->user()?->role, ['owner', 'admin'], true);
        }

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
            'status' => ['required', Rule::in(['submitted', 'approved', 'in_progress', 'completed', 'cancelled'])],
            'result' => ['nullable', 'string', 'max:10000', Rule::requiredIf($this->string('status')->toString() === 'completed')],
        ];
    }
}
