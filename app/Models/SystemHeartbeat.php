<?php

namespace App\Models;

use Database\Factories\SystemHeartbeatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemHeartbeat extends Model
{
    /** @use HasFactory<SystemHeartbeatFactory> */
    use HasFactory;

    protected $fillable = ['service', 'node', 'status', 'metadata', 'last_seen_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'last_seen_at' => 'datetime'];
    }

    public static function record(string $service, array $metadata = [], string $status = 'healthy'): self
    {
        return self::query()->updateOrCreate(
            ['service' => $service, 'node' => gethostname() ?: 'default'],
            ['status' => $status, 'metadata' => $metadata, 'last_seen_at' => now()]
        );
    }
}
