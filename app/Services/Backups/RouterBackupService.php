<?php

namespace App\Services\Backups;

use App\Models\BackupRun;
use App\Models\BackupSchedule;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\RouterBackupArtifact;
use App\Services\RouterOs\RouterOsClientFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

class RouterBackupService
{
    protected $exporter = null;

    protected $binaryBackuper = null;

    public function __construct(
        protected BackupDiffService $diffService,
        protected DiffAlertService $alertService,
        protected RouterOsClientFactory $clientFactory
    ) {}

    public function fakeExportUsing(?callable $exporter): void
    {
        $this->exporter = $exporter;
    }

    public function fakeBinaryBackupUsing(?callable $backuper): void
    {
        $this->binaryBackuper = $backuper;
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
        $backup->loadMissing(['router.passwordManagerCredential', 'schedule', 'artifacts']);
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

        if (! $router->backupsEnabled()) {
            $backup->forceFill([
                'status' => 'failed',
                'changed' => false,
                'finished_at' => now(),
                'error_message' => 'Backups are disabled for this router.',
            ])->save();

            return $backup;
        }

        $artifacts = [];

        if ($router->backup_rsc_enabled) {
            $artifacts[] = $this->processRscArtifact($backup, $router, $tenantId);
        }

        if ($router->backup_binary_enabled) {
            $artifacts[] = $this->processBinaryArtifact($backup, $router);
        }

        $successfulCount = collect($artifacts)->where('status', 'success')->count();
        $status = match (true) {
            $successfulCount === count($artifacts) => 'success',
            $successfulCount > 0 => 'partial_success',
            default => 'failed',
        };

        $errors = collect($artifacts)
            ->filter(fn (RouterBackupArtifact $artifact): bool => $artifact->status === 'failed')
            ->map(fn (RouterBackupArtifact $artifact): string => strtoupper($artifact->type).': '.$artifact->error_message)
            ->implode("\n");

        $backup->forceFill([
            'status' => $status,
            'routeros_version' => $router->version,
            'finished_at' => now(),
            'error_message' => $errors ?: null,
        ])->save();

        if ($successfulCount > 0) {
            $this->enforceRetention($router, $schedule);
        }

        return $backup->fresh(['artifacts', 'diff', 'alert']);
    }

    protected function processRscArtifact(RouterBackup $backup, Router $router, string $tenantId): RouterBackupArtifact
    {
        $artifact = $this->artifact($backup, 'rsc');

        try {
            $export = trim($this->export($router));
            $this->ensureValidExport($export);
            $content = $export."\n";
            $normalizedExport = $this->diffService->normalizeForComparison($content);
            $checksum = hash('sha256', $normalizedExport);
            $previous = RouterBackup::query()
                ->where('tenant_id', $tenantId)
                ->where('router_id', $router->id)
                ->whereIn('status', ['success', 'partial_success'])
                ->whereNotNull('path')
                ->latest('id')
                ->first();
            $changed = $previous === null || $previous->checksum !== $checksum;
            $path = $this->path($router, $backup, 'rsc');

            Storage::disk('local')->put($path, $content);
            $artifact->forceFill([
                'status' => 'success',
                'disk' => 'local',
                'path' => $path,
                'checksum' => $checksum,
                'size_bytes' => strlen($content),
                'error_message' => null,
            ])->save();
            $backup->forceFill([
                'previous_router_backup_id' => $previous?->id,
                'changed' => $changed,
                'disk' => 'local',
                'path' => $path,
                'checksum' => $checksum,
                'size_bytes' => strlen($content),
            ])->save();

            if ($changed && $previous?->path && Storage::disk($previous->disk)->exists($previous->path)) {
                $diff = $this->diffService->diff(Storage::disk($previous->disk)->get($previous->path), $content);
                $backupDiff = $backup->diff()->create([
                    'previous_router_backup_id' => $previous->id,
                    'added_lines' => $diff['added'],
                    'removed_lines' => $diff['removed'],
                    'unified_diff' => $diff['unified_diff'],
                    'hunks' => $diff['hunks'],
                ]);
                $this->alertService->createForDiff($backupDiff);
            }
        } catch (Throwable $throwable) {
            $artifact->forceFill(['status' => 'failed', 'error_message' => $throwable->getMessage()])->save();
        }

        return $artifact->fresh();
    }

    protected function processBinaryArtifact(RouterBackup $backup, Router $router): RouterBackupArtifact
    {
        $artifact = $this->artifact($backup, 'binary');

        try {
            $result = $this->binaryBackuper !== null
                ? call_user_func($this->binaryBackuper, $router, $backup)
                : $this->takeBinaryBackup($router, $backup);
            $artifact->forceFill([
                'status' => 'success',
                'disk' => 'local',
                'path' => $result['path'],
                'checksum' => $result['checksum'],
                'size_bytes' => $result['size_bytes'],
                'cleanup_error' => $result['cleanup_error'] ?? null,
                'error_message' => null,
            ])->save();
        } catch (Throwable $throwable) {
            $artifact->forceFill(['status' => 'failed', 'error_message' => $throwable->getMessage()])->save();
        }

        return $artifact->fresh();
    }

    protected function artifact(RouterBackup $backup, string $type): RouterBackupArtifact
    {
        return $backup->artifacts()->firstOrCreate(
            ['type' => $type],
            ['tenant_id' => $backup->tenant_id, 'status' => 'running', 'disk' => 'local']
        );
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

    protected function path(Router $router, RouterBackup $backup, string $extension): string
    {
        return 'router-backups/'.$router->tenant_id.'/'.$router->id.'/'.$backup->id.'/'.now()->format('Ymd_His').'_'.Str::random(8).'.'.$extension;
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
            ->whereIn('status', ['success', 'partial_success'])
            ->latest('id')
            ->skip($schedule->retention_count)
            ->take(PHP_INT_MAX)
            ->get()
            ->each(function (RouterBackup $backup): void {
                $backup->loadMissing('artifacts');
                $backup->artifacts->each(fn (RouterBackupArtifact $artifact) => $artifact->path
                    ? Storage::disk($artifact->disk)->delete($artifact->path)
                    : null);

                if ($backup->path && $backup->artifacts->isEmpty()) {
                    Storage::disk($backup->disk)->delete($backup->path);
                }

                $backup->delete();
            });
    }

    /**
     * @return array{path: string, checksum: string, size_bytes: int, cleanup_error: ?string}
     */
    protected function takeBinaryBackup(Router $router, RouterBackup $backup): array
    {
        if (! $router->enable_ssh) {
            throw new \RuntimeException('SSH must be enabled to transfer RouterOS binary backups.');
        }

        $remoteBase = 'skybase-'.$backup->id.'-'.Str::lower(Str::random(10));
        $remoteFile = $remoteBase.'.backup';
        $temporaryPath = tempnam(sys_get_temp_dir(), 'router-backup-');
        $cleanupError = null;
        $result = null;
        $stagingPath = null;

        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create a temporary server file.');
        }

        try {
            $this->runRouterSshCommand($router, '/system backup save name='.$remoteBase);
            $this->waitForStableRemoteFile($router, $remoteFile);
            $this->downloadRemoteFile($router, $remoteFile, $temporaryPath);
            $size = filesize($temporaryPath);

            if ($size === false || $size < 1) {
                throw new \RuntimeException('The transferred RouterOS binary backup is empty.');
            }

            $path = $this->path($router, $backup, 'backup');
            $stagingPath = $path.'.part';
            $stream = fopen($temporaryPath, 'rb');

            if ($stream === false || ! Storage::disk('local')->put($stagingPath, $stream)) {
                throw new \RuntimeException('Unable to store the RouterOS binary backup.');
            }

            if (is_resource($stream)) {
                fclose($stream);
            }

            if (! Storage::disk('local')->move($stagingPath, $path)) {
                Storage::disk('local')->delete($stagingPath);

                throw new \RuntimeException('Unable to finalize the RouterOS binary backup.');
            }

            $checksum = hash_file('sha256', $temporaryPath);

            if ($checksum === false) {
                Storage::disk('local')->delete($path);

                throw new \RuntimeException('Unable to checksum the RouterOS binary backup.');
            }

            $result = [
                'path' => $path,
                'checksum' => $checksum,
                'size_bytes' => $size,
                'cleanup_error' => null,
            ];
        } finally {
            @unlink($temporaryPath);

            if ($stagingPath !== null) {
                Storage::disk('local')->delete($stagingPath);
            }

            try {
                $this->runRouterSshCommand($router, '/file remove [find name="'.$remoteFile.'"]');
            } catch (Throwable $throwable) {
                $cleanupError = $throwable->getMessage();
                Log::warning('Unable to remove temporary RouterOS backup file.', [
                    'router_id' => $router->id,
                    'remote_file' => $remoteFile,
                    'error' => $cleanupError,
                ]);
            }
        }

        $result['cleanup_error'] = $cleanupError;

        return $result;
    }

    protected function waitForStableRemoteFile(Router $router, string $remoteFile): void
    {
        $previousSize = null;

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $output = trim($this->runRouterSshCommand($router, ':put [/file get [find name="'.$remoteFile.'"] size]'));
            $size = filter_var($output, FILTER_VALIDATE_INT);

            if ($size !== false && $size > 0 && $size === $previousSize) {
                return;
            }

            $previousSize = $size;
            usleep(500000);
        }

        throw new \RuntimeException('RouterOS binary backup did not become ready before the timeout.');
    }

    protected function runRouterSshCommand(Router $router, string $command): string
    {
        $config = $router->routerOsConfig();
        $process = Process::fromShellCommandline($this->sshCommand($router, $config, $command));
        $process->setTimeout($config['ssh_timeout']);
        $process->mustRun();

        return $process->getOutput();
    }

    protected function downloadRemoteFile(Router $router, string $remoteFile, string $temporaryPath): void
    {
        $config = $router->routerOsConfig();
        $options = sprintf(
            '-P %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -q -o ConnectTimeout=%d',
            $config['ssh_port'],
            $config['ssh_timeout']
        );
        $source = escapeshellarg($config['user'].'@'.$config['host'].':'.$remoteFile);
        $command = ($router->ssh_auth_method ?: 'private_key') === 'password'
            ? sprintf("sshpass -p '%s' scp %s %s %s", str_replace("'", "'\\''", $config['pass']), $options, $source, escapeshellarg($temporaryPath))
            : sprintf('scp %s -i %s %s %s', $options, escapeshellarg($config['ssh_private_key']), $source, escapeshellarg($temporaryPath));
        $process = Process::fromShellCommandline($command);
        $process->setTimeout($config['ssh_timeout']);
        $process->mustRun();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function sshCommand(Router $router, array $config, string $command): string
    {
        $options = sprintf(
            '-p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -q -T -o ConnectTimeout=%d',
            $config['ssh_port'],
            $config['ssh_timeout']
        );

        if (($router->ssh_auth_method ?: 'private_key') === 'password') {
            return sprintf(
                "sshpass -p '%s' ssh %s %s %s",
                str_replace("'", "'\\''", $config['pass']),
                $options,
                escapeshellarg($config['user'].'@'.$config['host']),
                escapeshellarg($command)
            );
        }

        return sprintf(
            'ssh %s -i %s %s %s',
            $options,
            escapeshellarg($config['ssh_private_key']),
            escapeshellarg($config['user'].'@'.$config['host']),
            escapeshellarg($command)
        );
    }
}
