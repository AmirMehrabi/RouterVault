<?php

namespace App\Services\Backups;

use App\Models\BackupRun;
use App\Models\BackupSchedule;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Services\RouterOs\RouterOsClientFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

class RouterBackupService
{
    protected $exporter = null;

    public function __construct(
        protected BackupDiffService $diffService,
        protected DiffAlertService $alertService,
        protected RouterOsClientFactory $clientFactory
    ) {}

    public function fakeExportUsing(?callable $exporter): void
    {
        $this->exporter = $exporter;
    }

    public function create(Router $router, ?BackupSchedule $schedule = null, ?BackupRun $run = null): RouterBackup
    {
        $backup = RouterBackup::query()->create([
            'tenant_id' => $router->tenant_id,
            'router_id' => $router->id,
            'backup_schedule_id' => $schedule?->id,
            'backup_run_id' => $run?->id,
            'status' => 'pending',
            'disk' => 'public',
        ]);

        return $this->process($backup);
    }

    public function process(RouterBackup $backup): RouterBackup
    {
        $backup->loadMissing(['router.passwordManagerCredential', 'schedule']);
        $router = $backup->router;
        $schedule = $backup->schedule;

        if ($router === null || $router->tenant_id !== $backup->tenant_id || ($schedule !== null && $schedule->tenant_id !== $backup->tenant_id)) {
            throw new \RuntimeException('Backup relationships do not belong to the same tenant.');
        }

        $tenantId = $backup->tenant_id;
        $backup->forceFill([
            'status' => 'running',
            'started_at' => now(),
            'finished_at' => null,
            'error_message' => null,
        ])->save();

        try {
            $export = $this->export($router);
            $export = trim($export);

            $this->ensureValidExport($export);

            $normalizedExport = $this->diffService->normalizeForComparison($export."\n");
            $checksum = hash('sha256', $normalizedExport);
            $previous = RouterBackup::query()
                ->where('tenant_id', $tenantId)
                ->where('router_id', $router->id)
                ->where('status', 'success')
                ->latest('id')
                ->first();
            $changed = $previous === null || $previous->checksum !== $checksum;
            $path = $this->path($router, $backup);

            Storage::disk($backup->disk)->put($path, $export."\n");

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

        Log::info('Router backup export started.', [
            'router_id' => $router->id,
            'tenant_id' => $router->tenant_id,
            'router_name' => $router->name,
            'host' => $config['host'],
            'ssh_port' => $config['ssh_port'],
            'ssh_auth_method' => $router->ssh_auth_method ?: 'private_key',
            'ssh_timeout' => $config['ssh_timeout'],
            'username_present' => filled($config['user'] ?? null),
            'password_present' => filled($config['pass'] ?? null),
            'private_key_present' => filled($config['ssh_private_key'] ?? null),
        ]);

        if ($this->hasApiCredentials($router) && ($router->ssh_auth_method ?: 'private_key') === 'private_key') {
            try {
                Log::info('Attempting RouterOS API export via library.', [
                    'router_id' => $router->id,
                    'tenant_id' => $router->tenant_id,
                    'router_name' => $router->name,
                    'host' => $config['host'],
                ]);

                return $this->exportViaLibrary($router);
            } catch (Throwable $throwable) {
                Log::warning('RouterOS API export failed, falling back to SSH.', [
                    'router_id' => $router->id,
                    'tenant_id' => $router->tenant_id,
                    'router_name' => $router->name,
                    'host' => $config['host'],
                    'error' => $throwable->getMessage(),
                ]);
            }
        }

        return $this->exportViaSsh($router);
    }

    protected function hasApiCredentials(Router $router): bool
    {
        return filled($router->resolvedApiUsername()) && filled($router->resolvedApiPassword());
    }

    protected function exportViaLibrary(Router $router): string
    {
        $client = $this->clientFactory->make($router);

        return $client->export();
    }

    protected function exportViaSsh(Router $router): string
    {
        $config = $router->routerOsConfig();

        // Build SSH command directly — spatie/ssh uses heredoc which RouterOS cannot parse.
        // Passing the command as a direct SSH argument lets RouterOS CLI interpret it correctly.
        $sshCommand = $this->buildSshCommand($router, $config);

        $process = Process::fromShellCommandline($sshCommand);
        $process->setTimeout($config['ssh_timeout']);
        $process->run();

        $stdoutOutput = trim($process->getOutput());
        $stderrOutput = trim($process->getErrorOutput());

        if (! $process->isSuccessful()) {
            Log::warning('Router backup SSH process exited with non-zero code.', [
                'router_id' => $router->id,
                'tenant_id' => $router->tenant_id,
                'router_name' => $router->name,
                'host' => $config['host'],
                'ssh_port' => $config['ssh_port'],
                'exit_code' => $process->getExitCode(),
                'stderr_output' => $stderrOutput,
                'stdout_length' => strlen($stdoutOutput),
                'stdout_preview' => Str::of($stdoutOutput)->limit(500)->toString(),
            ]);

            // RouterOS may exit with non-zero code even when the export command succeeded.
            // If we received meaningful output, treat it as a successful export.
            if ($stdoutOutput === '') {
                $error = $stderrOutput ?: 'RouterOS export command failed.';

                // Detect missing sshpass so the operator knows to install it.
                if (str_contains($stderrOutput, 'sshpass:') || str_contains($stderrOutput, 'command not found')) {
                    $error = 'sshpass is not installed. Install it with: apt-get install sshpass';
                }

                throw new \RuntimeException($error);
            }
        }

        Log::info('Router backup SSH export completed.', [
            'router_id' => $router->id,
            'tenant_id' => $router->tenant_id,
            'router_name' => $router->name,
            'host' => $config['host'],
            'ssh_port' => $config['ssh_port'],
            'output_bytes' => strlen($process->getOutput()),
        ]);

        return $process->getOutput();
    }

    /**
     * Build the SSH command for exporting RouterOS configuration.
     *
     * @param  array<string, mixed>  $config
     */
    private function buildSshCommand(Router $router, array $config): string
    {
        $host = $config['host'];
        $port = $config['ssh_port'];
        $user = $config['user'];
        $timeout = $config['ssh_timeout'];

        $options = sprintf(
            '-p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -q -T -o ConnectTimeout=%d',
            $port,
            $timeout
        );

        if (($router->ssh_auth_method ?: 'private_key') === 'password') {
            $pass = str_replace("'", "'\\''", $config['pass']);
            $sshCommand = sprintf(
                "sshpass -p '%s' ssh %s %s %s",
                $pass,
                $options,
                escapeshellarg($user.'@'.$host),
                escapeshellarg('/export')
            );
        } else {
            $sshCommand = sprintf(
                'ssh %s -i %s %s %s',
                $options,
                escapeshellarg($config['ssh_private_key']),
                escapeshellarg($user.'@'.$host),
                escapeshellarg('/export')
            );
        }

        return $sshCommand;
    }

    protected function ensureValidExport(string $export): void
    {
        if ($export === '') {
            throw new \RuntimeException('RouterOS export returned empty output.');
        }

        $firstLine = trim(Str::before($export, "\n"));
        $routerOsErrors = [
            'expected end of command',
            'expected command name',
            'expected value of',
            'syntax error',
            'bad command name',
            'failure:',
        ];

        foreach ($routerOsErrors as $routerOsError) {
            if (str_starts_with(strtolower($firstLine), $routerOsError)) {
                throw new \RuntimeException('RouterOS export failed: '.$firstLine);
            }
        }
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
