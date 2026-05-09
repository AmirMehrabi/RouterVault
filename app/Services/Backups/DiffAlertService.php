<?php

namespace App\Services\Backups;

use App\Models\DiffAlert;
use App\Models\DiffAlertSetting;
use App\Models\RouterBackupDiff;
use Illuminate\Support\Str;

class DiffAlertService
{
    public function createForDiff(RouterBackupDiff $diff): ?DiffAlert
    {
        $diff->loadMissing('backup.router', 'previousBackup');
        $backup = $diff->backup;
        $tenantId = $backup->tenant_id;
        $settings = DiffAlertSetting::forTenant($tenantId);

        if (! $settings->is_enabled) {
            return null;
        }

        $changedLines = $this->changedLines($diff->unified_diff, $settings->ignore_blank_lines);
        $sections = $this->sections($changedLines);
        $matches = $this->ignoredMatches($changedLines, $settings->ignored_sections ?? [], $settings->ignored_keywords ?? []);

        if ($changedLines === [] || $this->isSuppressed($sections, $changedLines, $settings, $matches)) {
            return null;
        }

        $severity = $this->severity($sections, $changedLines);
        $summary = "{$backup->router->name} configuration changed: {$diff->added_lines} added, {$diff->removed_lines} removed";

        return DiffAlert::query()->create([
            'tenant_id' => $tenantId,
            'router_id' => $backup->router_id,
            'router_backup_id' => $backup->id,
            'previous_router_backup_id' => $diff->previous_router_backup_id,
            'router_backup_diff_id' => $diff->id,
            'severity' => $severity,
            'status' => 'unread',
            'summary' => $summary,
            'sections' => $sections,
            'matched_ignored_patterns' => $matches,
            'added_lines' => $diff->added_lines,
            'removed_lines' => $diff->removed_lines,
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function changedLines(string $unifiedDiff, bool $ignoreBlankLines): array
    {
        return collect(explode("\n", $unifiedDiff))
            ->filter(fn (string $line): bool => (str_starts_with($line, '+') || str_starts_with($line, '-')) && ! str_starts_with($line, '@@'))
            ->map(fn (string $line): string => substr($line, 1))
            ->filter(fn (string $line): bool => ! $ignoreBlankLines || trim($line) !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $lines
     * @return array<int, string>
     */
    protected function sections(array $lines): array
    {
        return collect($lines)
            ->map(function (string $line): ?string {
                if (preg_match('#^/([a-z0-9-]+(?:/[a-z0-9-]+)*)#i', trim($line), $matches)) {
                    return strtolower($matches[1]);
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $lines
     * @param  array<int, string>  $ignoredSections
     * @param  array<int, string>  $ignoredKeywords
     * @return array<int, string>
     */
    protected function ignoredMatches(array $lines, array $ignoredSections, array $ignoredKeywords): array
    {
        $matches = [];
        $content = strtolower(implode("\n", $lines));

        foreach ([...$ignoredSections, ...$ignoredKeywords] as $pattern) {
            $pattern = trim((string) $pattern);
            if ($pattern !== '' && Str::contains($content, strtolower($pattern))) {
                $matches[] = $pattern;
            }
        }

        return array_values(array_unique($matches));
    }

    /**
     * @param  array<int, string>  $sections
     * @param  array<int, string>  $lines
     * @param  array<int, string>  $matches
     */
    protected function isSuppressed(array $sections, array $lines, DiffAlertSetting $settings, array $matches): bool
    {
        if ($matches === []) {
            return false;
        }

        $ignoredSections = array_filter($settings->ignored_sections ?? []);
        $ignoredKeywords = array_filter($settings->ignored_keywords ?? []);

        $allSectionsIgnored = $sections !== [] && collect($sections)->every(
            fn (string $section): bool => collect($ignoredSections)->contains(fn (string $ignored): bool => Str::contains($section, strtolower($ignored)))
        );
        $allLinesInIgnoredSections = collect($lines)->every(
            fn (string $line): bool => collect($ignoredSections)->contains(fn (string $ignored): bool => Str::contains(strtolower($line), strtolower($ignored)))
        );
        $allLinesIgnored = collect($lines)->every(
            fn (string $line): bool => collect($ignoredKeywords)->contains(fn (string $keyword): bool => Str::contains(strtolower($line), strtolower($keyword)))
        );

        return $allSectionsIgnored || $allLinesInIgnoredSections || $allLinesIgnored;
    }

    /**
     * @param  array<int, string>  $sections
     * @param  array<int, string>  $lines
     */
    protected function severity(array $sections, array $lines): string
    {
        $content = strtolower(implode(' ', [...$sections, ...$lines]));

        foreach (['firewall', 'user', 'service', 'ssh', 'api', 'winbox', 'certificate', 'route'] as $keyword) {
            if (Str::contains($content, $keyword)) {
                return 'high';
            }
        }

        foreach (['interface', 'bridge', 'vlan', 'ppp', 'queue', 'dhcp'] as $keyword) {
            if (Str::contains($content, $keyword)) {
                return 'medium';
            }
        }

        return 'low';
    }
}
