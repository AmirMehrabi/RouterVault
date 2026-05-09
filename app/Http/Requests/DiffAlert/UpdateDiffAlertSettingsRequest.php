<?php

namespace App\Http\Requests\DiffAlert;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiffAlertSettingsRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'is_enabled' => ['nullable', 'boolean'],
            'ignore_blank_lines' => ['nullable', 'boolean'],
            'ignored_sections' => ['nullable', 'string'],
            'ignored_keywords' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function normalized(): array
    {
        return [
            'is_enabled' => $this->boolean('is_enabled'),
            'ignore_blank_lines' => $this->boolean('ignore_blank_lines'),
            'ignored_sections' => $this->lines('ignored_sections'),
            'ignored_keywords' => $this->lines('ignored_keywords'),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function lines(string $key): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->input($key, '')))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
