<?php

namespace App\Services\Backups;

class BackupDiffService
{
    /**
     * @return array{added:int, removed:int, unified_diff:string, hunks:array<int, array<string, mixed>>}
     */
    public function diff(string $oldContent, string $newContent, int $context = 3): array
    {
        $oldContent = $this->normalizeForComparison($oldContent);
        $newContent = $this->normalizeForComparison($newContent);

        $oldLines = $this->splitLines($oldContent);
        $newLines = $this->splitLines($newContent);
        $operations = $this->operations($oldLines, $newLines);
        $groupedHunks = array_map(fn (array $hunk): array => $this->decorateHunk($hunk), $this->groupHunks($operations, $context));
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

    public function normalizeForComparison(string $content): string
    {
        $lines = $this->splitLines($content);

        if ($lines !== [] && $this->isRouterOsExportHeader($lines[0])) {
            array_shift($lines);
        }

        return $lines === [] ? '' : implode("\n", $lines)."\n";
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

    protected function isRouterOsExportHeader(string $line): bool
    {
        return preg_match('/^#\s+.+\s+by\s+RouterOS\s+\S+\s*$/i', trim($line)) === 1;
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

    /**
     * @param  array<string, mixed>  $hunk
     * @return array<string, mixed>
     */
    protected function decorateHunk(array $hunk): array
    {
        $lines = $hunk['lines'];
        $section = collect($lines)
            ->pluck('text')
            ->first(fn (string $line): bool => str_starts_with(trim($line), '/'));
        $splitRows = [];

        for ($index = 0; $index < count($lines);) {
            if ($lines[$index]['type'] === 'context') {
                $splitRows[] = ['old' => $lines[$index], 'new' => $lines[$index]];
                $index++;

                continue;
            }

            $removed = [];
            $added = [];

            while ($index < count($lines) && $lines[$index]['type'] !== 'context') {
                if ($lines[$index]['type'] === 'removed') {
                    $removed[] = $lines[$index];
                } else {
                    $added[] = $lines[$index];
                }

                $index++;
            }

            $rowCount = max(count($removed), count($added));

            for ($row = 0; $row < $rowCount; $row++) {
                $old = $removed[$row] ?? null;
                $new = $added[$row] ?? null;

                if ($old !== null && $new !== null) {
                    [$old['segments'], $new['segments']] = $this->intralineSegments($old['text'], $new['text']);
                }

                $splitRows[] = ['old' => $old, 'new' => $new];
            }
        }

        $hunk['section'] = $section ? trim($section) : 'Configuration';
        $hunk['split_rows'] = $splitRows;

        return $hunk;
    }

    /**
     * @return array{array<int, array{type:string,text:string}>, array<int, array{type:string,text:string}>}
     */
    protected function intralineSegments(string $old, string $new): array
    {
        $prefixLength = 0;
        $maximumPrefix = min(strlen($old), strlen($new));

        while ($prefixLength < $maximumPrefix && $old[$prefixLength] === $new[$prefixLength]) {
            $prefixLength++;
        }

        $suffixLength = 0;
        $maximumSuffix = min(strlen($old) - $prefixLength, strlen($new) - $prefixLength);

        while ($suffixLength < $maximumSuffix
            && $old[strlen($old) - $suffixLength - 1] === $new[strlen($new) - $suffixLength - 1]) {
            $suffixLength++;
        }

        $segments = function (string $value) use ($prefixLength, $suffixLength): array {
            $changedLength = strlen($value) - $prefixLength - $suffixLength;

            return array_values(array_filter([
                ['type' => 'context', 'text' => substr($value, 0, $prefixLength)],
                ['type' => 'changed', 'text' => substr($value, $prefixLength, $changedLength)],
                ['type' => 'context', 'text' => $suffixLength > 0 ? substr($value, -$suffixLength) : ''],
            ], fn (array $segment): bool => $segment['text'] !== ''));
        };

        return [$segments($old), $segments($new)];
    }
}
