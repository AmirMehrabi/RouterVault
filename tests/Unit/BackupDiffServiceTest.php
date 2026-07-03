<?php

namespace Tests\Unit;

use App\Services\Backups\BackupDiffService;
use PHPUnit\Framework\TestCase;

class BackupDiffServiceTest extends TestCase
{
    public function test_diff_exposes_section_split_rows_and_intraline_segments(): void
    {
        $diff = (new BackupDiffService)->diff(
            "/ip address\nadd address=10.0.0.1/24\n",
            "/ip address\nadd address=10.0.0.2/24\n"
        );

        $this->assertSame('/ip address', $diff['hunks'][0]['section']);
        $changedRow = collect($diff['hunks'][0]['split_rows'])
            ->first(fn (array $row): bool => ($row['old']['type'] ?? null) === 'removed');
        $this->assertNotNull($changedRow);
        $this->assertSame('changed', collect($changedRow['old']['segments'])->firstWhere('type', 'changed')['type']);
        $this->assertSame('changed', collect($changedRow['new']['segments'])->firstWhere('type', 'changed')['type']);
    }

    public function test_diff_does_not_cascade_changes_after_a_deletion(): void
    {
        $old = "line one\nline two\nline three\nline four\n";
        $new = "line one\nline three\nline four\n";

        $diff = (new BackupDiffService)->diff($old, $new);

        $this->assertSame(0, $diff['added']);
        $this->assertSame(1, $diff['removed']);
        $this->assertStringContainsString('-line two', $diff['unified_diff']);
        $this->assertStringNotContainsString('-line three', $diff['unified_diff']);
        $this->assertStringNotContainsString('+line three', $diff['unified_diff']);
    }

    public function test_diff_ignores_routeros_export_timestamp_header(): void
    {
        $old = "# 2026-05-01 15:46:35 by RouterOS 7.20.6\n/system identity set name=core\n";
        $new = "# 2026-05-01 15:46:01 by RouterOS 7.20.6\n/system identity set name=core\n";

        $diff = (new BackupDiffService)->diff($old, $new);

        $this->assertSame(0, $diff['added']);
        $this->assertSame(0, $diff['removed']);
        $this->assertSame('', $diff['unified_diff']);
        $this->assertSame([], $diff['hunks']);
    }

    public function test_normalized_comparison_content_excludes_routeros_export_timestamp_header(): void
    {
        $service = new BackupDiffService;
        $old = "# 2026-05-01 15:46:35 by RouterOS 7.20.6\n/system identity set name=core\n";
        $new = "# 2026-05-01 15:46:01 by RouterOS 7.20.6\n/system identity set name=core\n";

        $this->assertSame(
            hash('sha256', $service->normalizeForComparison($old)),
            hash('sha256', $service->normalizeForComparison($new))
        );
    }

    public function test_diff_ignores_routeros_v6_export_timestamp_header(): void
    {
        $old = "# jul/02/2026 08:23:20 by RouterOS 6.49.17\n/system identity set name=core\n";
        $new = "# jul/02/2026 08:22:47 by RouterOS 6.49.17\n/system identity set name=core\n";

        $diff = (new BackupDiffService)->diff($old, $new);

        $this->assertSame(0, $diff['added']);
        $this->assertSame(0, $diff['removed']);
        $this->assertSame('', $diff['unified_diff']);
        $this->assertSame([], $diff['hunks']);
    }

    public function test_diff_ignores_generated_headers_across_routeros_version_families(): void
    {
        $headerPairs = [
            ['# jan/01/2013 00:00:01 by RouterOS 5.26', '# jan/01/2013 00:01:02 by RouterOS 5.26'],
            ['# dec/31/2021 23:59:58 by RouterOS 6.48.6', '# jan/01/2022 00:00:02 by RouterOS 6.48.6'],
            ['# jul/02/2026 08:22:47 by RouterOS 6.49.17', '# jul/02/2026 08:23:20 by RouterOS 6.49.17'],
            ['# 2022-01-01 00:00:01 by RouterOS 7.1', '# 2022-01-01 00:01:02 by RouterOS 7.1'],
            ['# 2026-07-02 08:22:47 by RouterOS 7.20.6', '# 2026-07-02 08:23:20 by RouterOS 7.20.6'],
            ['# 2026-07-02 08:22:47 by RouterOS 7.20.6-long-term', '# 2026-07-02 08:23:20 by RouterOS 7.20.6-long-term'],
        ];

        $service = new BackupDiffService;

        foreach ($headerPairs as [$oldHeader, $newHeader]) {
            $old = "{$oldHeader}\n/system identity set name=core\n";
            $new = "{$newHeader}\n/system identity set name=core\n";
            $diff = $service->diff($old, $new);

            $this->assertSame(0, $diff['added'], $oldHeader);
            $this->assertSame(0, $diff['removed'], $oldHeader);
            $this->assertSame([], $diff['hunks'], $oldHeader);
            $this->assertSame(
                hash('sha256', $service->normalizeForComparison($old)),
                hash('sha256', $service->normalizeForComparison($new)),
                $oldHeader
            );
        }
    }
}
