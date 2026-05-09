<?php

namespace App\Services\Backups;

class BackupDiffService
{
    /**
     * @return array{added:int, removed:int, unified_diff:string, hunks:array<int, array<string, mixed>>}
     */
    public function diff(string $oldContent, string $newContent, int $context = 3): array
    {
        $oldLines = $this->splitLines($oldContent);
        $newLines = $this->splitLines($newContent);
        $operations = $this->operations($oldLines, $newLines);
        $groupedHunks = $this->groupHunks($operations, $context);
        $unified = [];
        $added = 0;
        $removed = 0;

        foreach ($groupedHunks as $hunk) {
            $oldStart = max(1, $hunk['old_start']);
            $newStart = max(1, $hunk['new_start']);
            $unified[] = "@@ -{$oldStart},{$hunk['old_count']} +{$newStart},{$hunk['new_count']} @@";

            foreach ($hunk['lines'] as $line) {
                if ($line['type'] === 'added') {
                    $added++;
                    $unified[] = '+'.$line['text'];
                } elseif ($line['type'] === 'removed') {
                    $removed++;
                    $unified[] = '-'.$line['text'];
                } else {
                    $unified[] = ' '.$line['text'];
                }
            }
        }

        return [
            'added' => $added,
            'removed' => $removed,
            'unified_diff' => implode("\n", $unified),
            'hunks' => $groupedHunks,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function splitLines(string $content): array
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        if ($content === '') {
            return [];
        }

        return explode("\n", rtrim($content, "\n"));
    }

    /**
     * @param  array<int, string>  $oldLines
     * @param  array<int, string>  $newLines
     * @return array<int, array{type:string, text:string, old_line:int|null, new_line:int|null}>
     */
    protected function operations(array $oldLines, array $newLines): array
    {
        $oldCount = count($oldLines);
        $newCount = count($newLines);
        $table = array_fill(0, $oldCount + 1, array_fill(0, $newCount + 1, 0));

        for ($i = $oldCount - 1; $i >= 0; $i--) {
            for ($j = $newCount - 1; $j >= 0; $j--) {
                $table[$i][$j] = $oldLines[$i] === $newLines[$j]
                    ? $table[$i + 1][$j + 1] + 1
                    : max($table[$i + 1][$j], $table[$i][$j + 1]);
            }
        }

        $operations = [];
        $i = 0;
        $j = 0;

        while ($i < $oldCount || $j < $newCount) {
            if ($i < $oldCount && $j < $newCount && $oldLines[$i] === $newLines[$j]) {
                $operations[] = ['type' => 'context', 'text' => $oldLines[$i], 'old_line' => $i + 1, 'new_line' => $j + 1];
                $i++;
                $j++;
            } elseif ($j < $newCount && ($i === $oldCount || $table[$i][$j + 1] >= $table[$i + 1][$j])) {
                $operations[] = ['type' => 'added', 'text' => $newLines[$j], 'old_line' => null, 'new_line' => $j + 1];
                $j++;
            } else {
                $operations[] = ['type' => 'removed', 'text' => $oldLines[$i], 'old_line' => $i + 1, 'new_line' => null];
                $i++;
            }
        }

        return $operations;
    }

    /**
     * @param  array<int, array{type:string, text:string, old_line:int|null, new_line:int|null}>  $operations
     * @return array<int, array<string, mixed>>
     */
    protected function groupHunks(array $operations, int $context): array
    {
        $changeIndexes = array_keys(array_filter($operations, fn (array $line): bool => $line['type'] !== 'context'));

        if ($changeIndexes === []) {
            return [];
        }

        $ranges = [];
        foreach ($changeIndexes as $index) {
            $start = max(0, $index - $context);
            $end = min(count($operations) - 1, $index + $context);
            $last = array_key_last($ranges);

            if ($last !== null && $start <= $ranges[$last][1] + 1) {
                $ranges[$last][1] = max($ranges[$last][1], $end);
            } else {
                $ranges[] = [$start, $end];
            }
        }

        return array_map(function (array $range) use ($operations): array {
            $lines = array_slice($operations, $range[0], $range[1] - $range[0] + 1);
            $oldLineNumbers = array_values(array_filter(array_column($lines, 'old_line')));
            $newLineNumbers = array_values(array_filter(array_column($lines, 'new_line')));

            return [
                'old_start' => $oldLineNumbers[0] ?? 1,
                'new_start' => $newLineNumbers[0] ?? 1,
                'old_count' => count(array_filter($lines, fn (array $line): bool => $line['type'] !== 'added')),
                'new_count' => count(array_filter($lines, fn (array $line): bool => $line['type'] !== 'removed')),
                'lines' => $lines,
            ];
        }, $ranges);
    }
}
