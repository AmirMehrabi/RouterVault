<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'timezone'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => 'company name',
            'phone' => 'phone number',
        ];
    }
}
