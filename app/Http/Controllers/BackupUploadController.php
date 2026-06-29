<?php

namespace App\Http\Controllers;

use App\Models\BackupToken;
use App\Models\RouterBackup;
use App\Services\Backups\BackupDiffService;
use App\Services\Backups\DiffAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupUploadController extends Controller
{
    public function upload(Request $request, BackupDiffService $diffService, DiffAlertService $alertService): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'Authorization token required.'], 401);
        }

        $backupToken = BackupToken::findByToken($token);

        if (! $backupToken) {
            return response()->json(['error' => 'Invalid or inactive token.'], 401);
        }

        $request->validate([
            'config' => ['required', 'file', 'mimes:rsc,txt', 'max:10240'],
        ]);

        $router = $backupToken->router;
        $tenantId = $router->tenant_id;

        $backup = RouterBackup::query()->create([
            'tenant_id' => $tenantId,
            'router_id' => $router->id,
            'status' => 'running',
            'disk' => 'local',
            'started_at' => now(),
        ]);

        try {
            $export = file_get_contents($request->file('config')->getRealPath());
            $export = trim($export);

            if ($export === '') {
                throw new \RuntimeException('Uploaded config file is empty.');
            }

            $normalizedExport = $diffService->normalizeForComparison($export."\n");
            $checksum = hash('sha256', $normalizedExport);

            $previous = RouterBackup::query()
                ->where('tenant_id', $tenantId)
                ->where('router_id', $router->id)
                ->where('status', 'success')
                ->latest('id')
                ->first();

            $changed = $previous === null || $previous->checksum !== $checksum;
            $path = 'router-backups/'.$tenantId.'/'.$router->id.'/'.now()->format('Ymd_His').'_push_backup_'.$backup->id.'_'.Str::random(8).'.rsc';

            Storage::disk('local')->put($path, $export."\n");

            $backup->forceFill([
                'previous_router_backup_id' => $previous?->id,
                'status' => 'success',
                'changed' => $changed,
                'path' => $path,
                'checksum' => $checksum,
                'size_bytes' => strlen($export."\n"),
                'finished_at' => now(),
            ])->save();

            if ($changed && $previous !== null) {
                $oldContent = Storage::disk($previous->disk)->get($previous->path);
                $diff = $diffService->diff($oldContent, $export."\n");
                $backupDiff = $backup->diff()->create([
                    'previous_router_backup_id' => $previous->id,
                    'added_lines' => $diff['added'],
                    'removed_lines' => $diff['removed'],
                    'unified_diff' => $diff['unified_diff'],
                    'hunks' => $diff['hunks'],
                ]);

                $alertService->createForDiff($backupDiff);
            }

            $backupToken->markUsed();

            return response()->json([
                'success' => true,
                'backup_id' => $backup->id,
                'changed' => $changed,
                'message' => $changed ? 'Configuration changes detected and backed up.' : 'Configuration unchanged. Backup stored.',
            ]);

        } catch (\Throwable $e) {
            $backup->forceFill([
                'status' => 'failed',
                'changed' => false,
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
            ])->save();

            Log::error('Backup upload failed', [
                'router_id' => $router->id,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Backup processing failed.',
            ], 500);
        }
    }
}
