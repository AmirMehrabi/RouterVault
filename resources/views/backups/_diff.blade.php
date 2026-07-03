@php
    $hunks = $hunks ?? [];
    $added = $diff['added'] ?? collect($hunks)->sum(fn ($hunk) => collect($hunk['lines'])->where('type', 'added')->count());
    $removed = $diff['removed'] ?? collect($hunks)->sum(fn ($hunk) => collect($hunk['lines'])->where('type', 'removed')->count());
    $viewerId = 'diff-'.\Illuminate\Support\Str::random(8);
@endphp

<div
    id="{{ $viewerId }}"
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    x-data="{
        mode: 'unified',
        changesOnly: false,
        query: '',
        hunk: 0,
        hunkCount: {{ count($hunks) }},
        matches(text, type) {
            return (!this.changesOnly || type !== 'context')
                && (!this.query || text.toLowerCase().includes(this.query.toLowerCase()));
        },
        jump(direction) {
            if (!this.hunkCount) return;
            this.hunk = (this.hunk + direction + this.hunkCount) % this.hunkCount;
            document.getElementById('{{ $viewerId }}-hunk-' + this.hunk)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }"
>
    <div class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex flex-col gap-3 px-4 py-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900">router-export.rsc</p>
                    <p class="text-xs text-slate-500">{{ count($hunks) }} {{ \Illuminate\Support\Str::plural('change block', count($hunks)) }}</p>
                </div>
                <div class="hidden items-center gap-2 sm:flex">
                    <span class="font-mono text-xs font-semibold text-emerald-700">+{{ $added }}</span>
                    <span class="font-mono text-xs font-semibold text-red-700">−{{ $removed }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5">
                    <button type="button" @click="mode = 'unified'" :class="mode === 'unified' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600'" class="rounded-md px-3 py-1.5 text-xs font-semibold">Unified</button>
                    <button type="button" @click="mode = 'split'" :class="mode === 'split' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600'" class="hidden rounded-md px-3 py-1.5 text-xs font-semibold lg:block">Split</button>
                </div>
                <label class="relative min-w-48 flex-1 sm:flex-none">
                    <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input x-model.debounce.150ms="query" type="search" placeholder="Search in diff…" class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-xs text-slate-700 focus:border-blue-500 focus:ring-blue-500 sm:w-56">
                </label>
                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600">
                    <input x-model="changesOnly" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Changes only
                </label>
                <div class="inline-flex overflow-hidden rounded-lg border border-slate-200">
                    <button type="button" @click="jump(-1)" class="border-r border-slate-200 p-2 text-slate-500 hover:bg-slate-50" title="Previous change"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m18 15-6-6-6 6"/></svg></button>
                    <button type="button" @click="jump(1)" class="p-2 text-slate-500 hover:bg-slate-50" title="Next change"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg></button>
                </div>
            </div>
        </div>
    </div>

    @forelse($hunks as $hunkIndex => $hunk)
        <section id="{{ $viewerId }}-hunk-{{ $hunkIndex }}" class="border-b border-slate-200 last:border-b-0">
            <div class="flex items-center justify-between border-b border-blue-100 bg-blue-50/70 px-4 py-2 font-mono text-xs text-blue-800">
                <span class="truncate font-semibold">{{ $hunk['section'] ?? 'Configuration' }}</span>
                <span class="ml-4 shrink-0 text-blue-600">@@ -{{ $hunk['old_start'] }},{{ $hunk['old_count'] }} +{{ $hunk['new_start'] }},{{ $hunk['new_count'] }} @@</span>
            </div>

            <div x-show="mode === 'unified'" class="overflow-x-auto">
                <table class="min-w-full border-collapse font-mono text-[12px] leading-5 text-slate-800">
                    <tbody>
                        @foreach($hunk['lines'] as $line)
                            @php
                                $rowClass = match ($line['type']) {
                                    'added' => 'bg-emerald-50/80',
                                    'removed' => 'bg-red-50/80',
                                    default => 'bg-white',
                                };
                                $markerClass = match ($line['type']) {
                                    'added' => 'bg-emerald-100 text-emerald-800',
                                    'removed' => 'bg-red-100 text-red-800',
                                    default => 'text-slate-400',
                                };
                                $prefix = $line['type'] === 'added' ? '+' : ($line['type'] === 'removed' ? '−' : ' ');
                            @endphp
                            <tr x-show="matches(@js($line['text']), @js($line['type']))" class="{{ $rowClass }}">
                                <td class="w-14 select-none border-r border-slate-100 px-2 text-right text-slate-400">{{ $line['old_line'] ?? '' }}</td>
                                <td class="w-14 select-none border-r border-slate-100 px-2 text-right text-slate-400">{{ $line['new_line'] ?? '' }}</td>
                                <td class="w-8 select-none px-2 text-center font-bold {{ $markerClass }}">{{ $prefix }}</td>
                                <td class="whitespace-pre px-2 py-0.5">{{ $line['text'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div x-show="mode === 'split'" x-cloak class="hidden overflow-x-auto lg:block">
                <table class="w-full table-fixed border-collapse font-mono text-[12px] leading-5 text-slate-800">
                    <tbody>
                        @foreach($hunk['split_rows'] ?? [] as $row)
                            @php
                                $searchText = trim(($row['old']['text'] ?? '').' '.($row['new']['text'] ?? ''));
                                $rowType = ($row['old']['type'] ?? $row['new']['type'] ?? 'context');
                            @endphp
                            <tr x-show="matches(@js($searchText), @js($rowType))">
                                @foreach(['old', 'new'] as $side)
                                    @php
                                        $line = $row[$side];
                                        $isOld = $side === 'old';
                                        $cellClass = $line === null
                                            ? 'bg-slate-50'
                                            : (($line['type'] ?? 'context') === 'removed'
                                                ? 'bg-red-50'
                                                : (($line['type'] ?? 'context') === 'added' ? 'bg-emerald-50' : 'bg-white'));
                                    @endphp
                                    <td class="w-12 select-none border-r border-slate-100 px-2 text-right text-slate-400 {{ $cellClass }}">{{ $line[$isOld ? 'old_line' : 'new_line'] ?? '' }}</td>
                                    <td class="w-[calc(50%-3rem)] border-r border-slate-200 px-3 py-0.5 align-top {{ $cellClass }}">
                                        @if($line)
                                            <span class="mr-2 select-none font-bold {{ $line['type'] === 'removed' ? 'text-red-700' : ($line['type'] === 'added' ? 'text-emerald-700' : 'text-slate-300') }}">{{ $line['type'] === 'removed' ? '−' : ($line['type'] === 'added' ? '+' : ' ') }}</span>
                                            <span class="whitespace-pre">@if(isset($line['segments']))@foreach($line['segments'] as $segment)<span class="{{ $segment['type'] === 'changed' ? ($line['type'] === 'removed' ? 'rounded-sm bg-red-200' : 'rounded-sm bg-emerald-200') : '' }}">{{ $segment['text'] }}</span>@endforeach @else{{ $line['text'] }}@endif</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <div class="flex flex-col items-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg>
            </div>
            <h3 class="mt-4 text-sm font-semibold text-slate-900">No differences to show.</h3>
            <p class="mt-1 text-sm text-slate-500">These snapshots contain the same normalized RouterOS configuration.</p>
        </div>
    @endforelse
</div>
