<?php

namespace Tests\Unit;

use App\Services\Backups\BackupDiffService;
use PHPUnit\Framework\TestCase;

class BackupDiffServiceTest extends TestCase
{
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
}
