<?php

namespace App\Services\Operations;

use App\Models\SystemHeartbeat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SystemHealthService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function checks(): array
    {
        $checks = [
            $this->heartbeat('scheduler', 180),
            $this->heartbeat('queue', 180),
            $this->heartbeat('router-connectivity', 600),
            $this->heartbeat('backup-scheduler', 180),
            $this->database(),
            $this->storage(),
        ];

        return $checks;
    }

    /**
     * @return array{healthy:int, warning:int, critical:int}
     */
    public function summary(array $checks): array
    {
        return collect($checks)->countBy('status')->pipe(fn ($counts): array => [
            'healthy' => $counts->get('healthy', 0),
            'warning' => $counts->get('warning', 0),
            'critical' => $counts->get('critical', 0),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function heartbeat(string $service, int $staleAfterSeconds): array
    {
        $heartbeat = SystemHeartbeat::query()->where('service', $service)->latest('last_seen_at')->first();
        $age = $heartbeat?->last_seen_at?->diffInSeconds(now());
        $status = $heartbeat === null ? 'critical' : ($age > $staleAfterSeconds ? 'critical' : ($age > ($staleAfterSeconds / 2) ? 'warning' : 'healthy'));

        return [
            'key' => $service,
            'name' => str($service)->replace('-', ' ')->title()->toString(),
            'status' => $status,
            'message' => $heartbeat ? 'Last heartbeat '.$heartbeat->last_seen_at->diffForHumans().'.' : 'No heartbeat has been recorded.',
            'last_seen_at' => $heartbeat?->last_seen_at,
            'metadata' => $heartbeat?->metadata ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function database(): array
    {
        try {
            DB::select('select 1');

            return ['key' => 'database', 'name' => 'Database', 'status' => 'healthy', 'message' => 'Database query succeeded.', 'last_seen_at' => now(), 'metadata' => []];
        } catch (Throwable $throwable) {
            return ['key' => 'database', 'name' => 'Database', 'status' => 'critical', 'message' => $throwable->getMessage(), 'last_seen_at' => null, 'metadata' => []];
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function storage(): array
    {
        try {
            $path = 'health/.probe';
            Storage::disk('local')->put($path, now()->toIso8601String());
            Storage::disk('local')->delete($path);

            return ['key' => 'storage', 'name' => 'Private backup storage', 'status' => 'healthy', 'message' => 'Write and delete probe succeeded.', 'last_seen_at' => now(), 'metadata' => []];
        } catch (Throwable $throwable) {
            return ['key' => 'storage', 'name' => 'Private backup storage', 'status' => 'critical', 'message' => $throwable->getMessage(), 'last_seen_at' => null, 'metadata' => []];
        }
    }
}
