<?php

namespace App\Services\Backups;

use App\Models\BackupRun;
use App\Models\BackupSchedule;
use App\Models\Router;
use App\Models\RouterBackup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Ssh\Ssh;
use Throwable;

class RouterBackupService
{
    protected $exporter = null;

    public function __construct(
        protected BackupDiffService $diffService,
        protected DiffAlertService $alertService
    ) {}

    public function fakeExportUsing(?callable $exporter): void
    {
        $this->exporter = $exporter;
    }

    public function create(Router $router, ?BackupSchedule $schedule = null, ?BackupRun $run = null): RouterBackup
    {
        $tenantId = $router->tenant_id;
        $backup = RouterBackup::query()->create([
            'tenant_id' => $tenantId,
            'router_id' => $router->id,
            'backup_schedule_id' => $schedule?->id,
            'backup_run_id' => $run?->id,
            'status' => 'running',
            'disk' => 'local',
            'started_at' => now(),
        ]);

        try {
            $export = $this->export($router);
            $export = trim($export);

            if ($export === '') {
                throw new \RuntimeException('RouterOS export returned empty output.');
            }

            $checksum = hash('sha256', $export);
            $previous = RouterBackup::query()
                ->where('tenant_id', $tenantId)
                ->where('router_id', $router->id)
                ->where('status', 'success')
                ->latest('id')
                ->first();
            $changed = $previous === null || $previous->checksum !== $checksum;
            $path = $this->path($router, $backup);

            Storage::disk('local')->put($path, $export."\n");

            $backup->forceFill([
                'previous_router_backup_id' => $previous?->id,
                'status' => 'success',
                'changed' => $changed,
                'path' => $path,
                'checksum' => $checksum,
                'size_bytes' => strlen($export."\n"),
                'routeros_version' => $router->version,
                'finished_at' => now(),
            ])->save();

            if ($changed && $previous !== null) {
                $oldContent = Storage::disk($previous->disk)->get($previous->path);
                $diff = $this->diffService->diff($oldContent, $export."\n");
                $backupDiff = $backup->diff()->create([
                    'previous_router_backup_id' => $previous->id,
                    'added_lines' => $diff['added'],
                    'removed_lines' => $diff['removed'],
                    'unified_diff' => $diff['unified_diff'],
                    'hunks' => $diff['hunks'],
                ]);

                $this->alertService->createForDiff($backupDiff);
            }

            $this->enforceRetention($router, $schedule);

            return $backup->fresh(['diff', 'alert']);
        } catch (Throwable $throwable) {
            $backup->forceFill([
                'status' => 'failed',
                'changed' => false,
                'finished_at' => now(),
                'error_message' => $throwable->getMessage(),
            ])->save();

            return $backup;
        }
    }

    protected function export(Router $router): string
    {
        if ($this->exporter !== null) {
            return (string) call_user_func($this->exporter, $router);
        }

        $config = $router->routerOsConfig();
        $ssh = Ssh::create($config['user'], $config['host'], $config['ssh_port'])
            ->disableStrictHostKeyChecking()
            ->setTimeout($config['ssh_timeout']);

        if (($config['ssh_auth_method'] ?? 'private_key') === 'password') {
            $ssh->usePassword($config['pass']);
        } else {
            $ssh->usePrivateKey($config['ssh_private_key']);
        }

        $process = $ssh->execute('/export show-sensitive');

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: 'RouterOS export command failed.');
        }

        return $process->getOutput();
    }

    protected function path(Router $router, RouterBackup $backup): string
    {
        return 'router-backups/'.$router->tenant_id.'/'.$router->id.'/'.now()->format('Ymd_His').'_backup_'.$backup->id.'_'.Str::random(8).'.rsc';
    }

    protected function enforceRetention(Router $router, ?BackupSchedule $schedule): void
    {
        if ($schedule === null || $schedule->retention_count < 1) {
            return;
        }

        RouterBackup::query()
            ->where('tenant_id', $router->tenant_id)
            ->where('router_id', $router->id)
            ->where('backup_schedule_id', $schedule->id)
            ->where('status', 'success')
            ->latest('id')
            ->skip($schedule->retention_count)
            ->take(PHP_INT_MAX)
            ->get()
            ->each(function (RouterBackup $backup): void {
                if ($backup->path) {
                    Storage::disk($backup->disk)->delete($backup->path);
                }

                $backup->delete();
            });
    }
}
