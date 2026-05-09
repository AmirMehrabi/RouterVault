@php
    $hunks = $hunks ?? [];
@endphp
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
    @forelse($hunks as $hunk)
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-2 font-mono text-xs text-gray-600">
            @@ -{{ $hunk['old_start'] }},{{ $hunk['old_count'] }} +{{ $hunk['new_start'] }},{{ $hunk['new_count'] }} @@
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full font-mono text-xs">
                <tbody>
                    @foreach($hunk['lines'] as $line)
                        @php
                            $class = match ($line['type']) {
                                'added' => 'bg-white text-green-800',
                                'removed' => 'bg-red-50 text-red-800',
                                default => 'bg-white text-gray-700',
                            };
                            $prefix = $line['type'] === 'added' ? '+' : ($line['type'] === 'removed' ? '-' : ' ');
                        @endphp
                        <tr class="{{ $class }}">
                            <td class="w-14 select-none border-r border-gray-100 px-2 py-1 text-right text-gray-400">{{ $line['old_line'] ?? '' }}</td>
                            <td class="w-14 select-none border-r border-gray-100 px-2 py-1 text-right text-gray-400">{{ $line['new_line'] ?? '' }}</td>
                            <td class="w-6 select-none px-2 py-1 text-gray-500">{{ $prefix }}</td>
                            <td class="whitespace-pre px-2 py-1">{{ $line['text'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="px-4 py-8 text-center text-sm text-gray-500">No differences to show.</div>
    @endforelse
</div>
