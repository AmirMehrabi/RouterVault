<?php

namespace App\Services\Operations;

use App\Models\ComplianceFinding;
use App\Models\ConfigurationBaseline;
use App\Models\Router;
use App\Models\RouterBackup;
use Illuminate\Support\Facades\Storage;

class ComplianceService
{
    /**
     * @return array<int, ComplianceFinding>
     */
    public function evaluate(Router $router): array
    {
        $backup = RouterBackup::query()
            ->where('router_id', $router->id)
            ->whereIn('status', ['success', 'partial_success'])
            ->whereNotNull('path')
            ->latest()
            ->first();
        $content = $backup && Storage::disk($backup->disk)->exists($backup->path)
            ? strtolower(Storage::disk($backup->disk)->get($backup->path))
            : '';
        $baseline = ConfigurationBaseline::query()->where('router_id', $router->id)->with('backup')->first();
        $rules = [
            'recent_backup' => [
                'name' => 'Recent recoverable export',
                'status' => $backup?->created_at?->gte(now()->subDay()) ? 'compliant' : 'critical',
                'summary' => $backup ? 'Latest text export is '.$backup->created_at->diffForHumans().'.' : 'No recoverable text export exists.',
                'remediation' => 'Run and verify a new .rsc backup.',
            ],
            'approved_baseline' => [
                'name' => 'Approved configuration baseline',
                'status' => $baseline === null ? 'warning' : ($baseline->backup?->checksum === $backup?->checksum ? 'compliant' : 'warning'),
                'summary' => $baseline === null ? 'No approved baseline has been selected.' : ($baseline->backup?->checksum === $backup?->checksum ? 'Current export matches the approved baseline.' : 'Current export differs from the approved baseline.'),
                'remediation' => 'Review the latest diff and approve a known-good backup.',
            ],
            'insecure_services' => [
                'name' => 'Insecure management services',
                'status' => $this->hasInsecureServices($content) ? 'critical' : ($content === '' ? 'unknown' : 'compliant'),
                'summary' => $this->hasInsecureServices($content) ? 'Telnet, FTP, or unencrypted web management appears enabled.' : 'No explicitly enabled insecure management service was detected.',
                'remediation' => 'Disable Telnet, FTP, and HTTP management; use SSH and HTTPS/API-SSL.',
            ],
            'routeros_version' => [
                'name' => 'RouterOS version inventory',
                'status' => filled($router->version) ? 'compliant' : 'warning',
                'summary' => filled($router->version) ? 'Reported version: '.$router->version.'.' : 'RouterOS version is unknown.',
                'remediation' => 'Restore connectivity and refresh router inventory.',
            ],
        ];

        return collect($rules)->map(function (array $rule, string $key) use ($router, $backup): ComplianceFinding {
            return ComplianceFinding::query()->updateOrCreate(
                ['router_id' => $router->id, 'rule_key' => $key],
                [
                    'tenant_id' => $router->tenant_id,
                    'router_backup_id' => $backup?->id,
                    'rule_name' => $rule['name'],
                    'status' => $rule['status'],
                    'summary' => $rule['summary'],
                    'remediation' => $rule['remediation'],
                    'checked_at' => now(),
                ]
            );
        })->values()->all();
    }

    protected function hasInsecureServices(string $content): bool
    {
        return str_contains($content, 'set telnet disabled=no')
            || str_contains($content, 'set ftp disabled=no')
            || str_contains($content, 'set www disabled=no');
    }
}
