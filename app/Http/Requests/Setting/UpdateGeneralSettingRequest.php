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
            'timezone' => ['required', 'string', 'timezone'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}
