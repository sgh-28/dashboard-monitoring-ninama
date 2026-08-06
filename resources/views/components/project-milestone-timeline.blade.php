@php
    $items = collect($timelineData['items'] ?? []);
    $alerts = collect($timelineData['alerts'] ?? []);
    $start = isset($timelineData['start']) ? \Carbon\Carbon::parse($timelineData['start'])->startOfDay() : now()->startOfDay();
    $end = isset($timelineData['end']) ? \Carbon\Carbon::parse($timelineData['end'])->startOfDay() : $start->copy();

    if ($end->lt($start)) {
        $end = $start->copy();
    }

    $totalDays = max(1, $start->diffInDays($end));
    $markers = [];
    $cursor = $start->copy();

    while ($cursor->lte($end)) {
        $markers[] = [
            'label' => $cursor->translatedFormat('j M'),
            'left' => min(100, max(0, ($start->diffInDays($cursor) / $totalDays) * 100)),
        ];
        $cursor->addDays(7);
    }

    if (empty($markers) || end($markers)['left'] < 100) {
        $markers[] = [
            'label' => $end->translatedFormat('j M'),
            'left' => 100,
        ];
    }

    $formatCompactRange = function (?string $startDate, ?string $endDate) {
        if (!$startDate) {
            return '-';
        }

        $from = \Carbon\Carbon::parse($startDate);
        $to = $endDate ? \Carbon\Carbon::parse($endDate) : null;

        if (!$to) {
            return $from->translatedFormat('j M') . ' - Berjalan';
        }

        if ($from->isSameMonth($to) && $from->isSameYear($to)) {
            return $from->translatedFormat('j') . '-' . $to->translatedFormat('j M');
        }

        return $from->translatedFormat('j M') . '-' . $to->translatedFormat('j M');
    };
@endphp

<div class="inline-block max-w-full rounded-lg border border-gray-800 bg-[#111111] text-white overflow-hidden">
    <div class="px-5 py-4 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <h3 class="text-base font-semibold text-white">Milestone - {{ $start->translatedFormat('F') }}-{{ $end->translatedFormat('F Y') }}</h3>
            <p class="mt-1 text-xs text-gray-500">Rencana dan realisasi pekerjaan per divisi.</p>
        </div>

        <div class="flex flex-col gap-2 text-xs">
            <span class="inline-flex items-center gap-2 text-white"><span class="h-2 w-8 rounded-full bg-blue-500"></span>Rencana</span>
            <span class="inline-flex items-center gap-2 text-white"><span class="h-2 w-8 rounded-full bg-green-500"></span>Realisasi</span>
            <span class="inline-flex items-center gap-2 text-white"><span class="h-2 w-8 rounded-full bg-red-500"></span>Terlambat</span>
        </div>
    </div>

    @if($alerts->isNotEmpty())
        <div class="mx-5 mb-4 rounded-md border border-red-500/30 bg-red-500/10 p-3">
            <p class="text-sm font-semibold text-red-200">Pemberitahuan keterlambatan</p>
            <div class="mt-2 space-y-2">
                @foreach($alerts as $alert)
                    <div class="text-xs text-red-100">
                        <span class="font-semibold">{{ $alert['division'] }}</span> - {{ $alert['task'] }}
                        terlambat {{ $alert['delay_days'] }} hari.
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($items->isNotEmpty())
        <div class="px-5 pb-5 overflow-x-auto">
            <div class="w-[760px] max-w-[760px]">
                <div class="grid grid-cols-[210px_140px_220px_150px] gap-4 items-end border-b border-gray-800 pb-3 text-xs text-gray-300">
                    <div class="text-gray-400">Milestone</div>
                    <div></div>
                    <div class="relative h-7">
                        @foreach($markers as $marker)
                            <span class="absolute -translate-x-1/2 whitespace-nowrap" style="left: {{ $marker['left'] }}%;">
                                {{ $marker['label'] }}
                            </span>
                        @endforeach
                    </div>
                    <div></div>
                </div>

                <div class="divide-y divide-gray-800">
                    @foreach($items as $item)
                        @php
                            $plannedStart = \Carbon\Carbon::parse($item['planned_start']);
                            $plannedEnd = \Carbon\Carbon::parse($item['planned_end']);
                            $actualStart = $item['actual_start'] ? \Carbon\Carbon::parse($item['actual_start']) : null;
                            $actualEnd = $item['actual_end'] ? \Carbon\Carbon::parse($item['actual_end']) : null;
                            $dayDiff = $actualEnd ? (int) $actualEnd->diffInDays($plannedEnd, false) : null;
                            $resultText = 'Belum selesai';
                            $resultSubText = $item['status_label'];
                            $resultColor = 'text-gray-300';

                            if ($actualEnd) {
                                if ($dayDiff > 0) {
                                    $resultText = $dayDiff . ' hari lebih cepat';
                                    $resultSubText = 'Sesuai rencana';
                                    $resultColor = 'text-white';
                                } elseif ($dayDiff === 0) {
                                    $resultText = 'Tepat waktu';
                                    $resultSubText = 'Sesuai rencana';
                                    $resultColor = 'text-white';
                                } else {
                                    $resultText = abs($dayDiff) . ' hari terlambat';
                                    $resultSubText = 'Melewati rencana';
                                    $resultColor = 'text-red-300';
                                }
                            } elseif ($item['is_delayed']) {
                                $resultText = $item['delay_days'] . ' hari terlambat';
                                $resultSubText = 'Belum selesai';
                                $resultColor = 'text-red-300';
                            }
                        @endphp

                        <div class="grid grid-cols-[210px_140px_220px_150px] gap-4 py-5 items-center">
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $item['title'] }}</p>
                                <p class="mt-2 text-xs text-gray-400">{{ $item['division'] }} - {{ $item['status_label'] }}</p>
                            </div>

                            <div class="space-y-4 text-xs font-semibold">
                                <p><span class="text-white">Rencana</span> : {{ $formatCompactRange($item['planned_start'], $item['planned_end']) }}</p>
                                <p><span class="text-white">Realisasi</span> : {{ $formatCompactRange($item['actual_start'], $item['actual_end']) }}</p>
                            </div>

                            <div class="relative h-16">
                                @foreach($markers as $marker)
                                    <div class="absolute top-0 bottom-0 w-px bg-white/5" style="left: {{ $marker['left'] }}%;"></div>
                                @endforeach

                                <div class="absolute left-0 right-0 top-2 h-4 rounded-full bg-white/10"></div>
                                <div class="absolute top-2 h-4 rounded-full bg-blue-500 shadow-sm shadow-blue-500/30"
                                     style="left: {{ $item['planned']['left'] }}%; width: {{ $item['planned']['width'] }}%;"></div>
                                <div class="absolute top-1 h-6 w-2 rounded-full border border-blue-400/80"
                                     style="left: calc({{ $item['planned']['left'] }}% - 4px);"></div>
                                <div class="absolute top-1 h-6 w-2 rounded-full border border-blue-400/80"
                                     style="left: calc({{ $item['planned']['left'] + $item['planned']['width'] }}% - 4px);"></div>

                                <div class="absolute left-0 right-0 bottom-2 h-4 rounded-full bg-white/10"></div>
                                @if($item['actual'])
                                    <div class="absolute bottom-2 h-4 rounded-full {{ $item['is_delayed'] ? 'bg-red-500 shadow-sm shadow-red-500/30' : 'bg-green-500 shadow-sm shadow-green-500/30' }}"
                                         style="left: {{ $item['actual']['left'] }}%; width: {{ $item['actual']['width'] }}%;"></div>
                                    <div class="absolute bottom-1 h-6 w-2 rounded-full border {{ $item['is_delayed'] ? 'border-red-400/80' : 'border-green-400/80' }}"
                                         style="left: calc({{ $item['actual']['left'] }}% - 4px);"></div>
                                    <div class="absolute bottom-1 h-6 w-2 rounded-full border {{ $item['is_delayed'] ? 'border-red-400/80' : 'border-green-400/80' }}"
                                         style="left: calc({{ $item['actual']['left'] + $item['actual']['width'] }}% - 4px);"></div>
                                @endif
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-bold {{ $resultColor }}">{{ $resultText }}</p>
                                <p class="mt-2 text-xs text-gray-400">{{ $resultSubText }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="p-8 text-center text-gray-400">
            Timeline akan muncul setelah admin membuat task untuk divisi proyek.
        </div>
    @endif
</div>
