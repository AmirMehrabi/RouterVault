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
}
