<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiffAlertSetting extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'is_enabled',
        'ignore_blank_lines',
        'ignored_sections',
        'ignored_keywords',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'ignore_blank_lines' => 'boolean',
            'ignored_sections' => 'array',
            'ignored_keywords' => 'array',
        ];
    }

    public static function forTenant(string $tenantId): self
    {
        return self::query()->firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'is_enabled' => true,
                'ignore_blank_lines' => true,
                'ignored_sections' => [],
                'ignored_keywords' => [],
            ]
        );
    }
}
