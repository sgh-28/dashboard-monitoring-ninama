@php
    $items = collect($timelineData['items'] ?? []);
    $alerts = collect($timelineData['alerts'] ?? []);
    $start = isset($timelineData['start']) ? \Carbon\Carbon::parse($timelineData['start'])->startOfDay() : now()->startOfDay();
    $end = isset($timelineData['end']) ? \Carbon\Carbon::parse($timelineData['end'])->startOfDay() : $start->copy();

    if ($end->lt($start)) {
        $end = $start->copy();
    }

    foreach ($items as $timelineItem) {
        foreach (['planned_start', 'planned_end', 'actual_start', 'actual_end'] as $dateKey) {
            if (!empty($timelineItem[$dateKey])) {
                $date = \Carbon\Carbon::parse($timelineItem[$dateKey])->startOfDay();
                if ($date->lt($start)) {
                    $start = $date->copy();
                }
                if ($date->gt($end)) {
                    $end = $date->copy();
                }
            }
        }
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

    $barPosition = function ($fromDate, $toDate) use ($start, $totalDays) {
        $from = \Carbon\Carbon::parse($fromDate)->startOfDay();
        $to = \Carbon\Carbon::parse($toDate)->startOfDay();

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        $left = min(100, max(0, ($start->diffInDays($from, false) / $totalDays) * 100));
        $width = max(3, ($from->diffInDays($to) / $totalDays) * 100);

        return [
            'left' => $left,
            'width' => min(100 - $left, $width),
        ];
    };
@endphp

<div class="inline-block max-w-full rounded-lg border border-white/80 bg-[#111111] text-white overflow-hidden">
    <div class="w-[760px] max-w-[760px] px-5 py-5">
        <div class="grid grid-cols-[210px_140px_220px_150px] gap-4 items-start">
            <div class="col-span-2">
                <h3 class="text-base font-semibold text-white">Milestone - {{ $start->translatedFormat('F') }}-{{ $end->translatedFormat('F Y') }}</h3>
                <p class="mt-2 text-sm text-gray-500">Rencana dan realisasi pekerjaan per divisi.</p>
            </div>

            <div></div>

            <div class="flex flex-col gap-3 text-sm">
                <span class="inline-flex items-center gap-3 text-white"><span class="h-2 w-10 rounded-full bg-blue-500"></span>Rencana</span>
                <span class="inline-flex items-center gap-3 text-white"><span class="h-2 w-10 rounded-full bg-green-500"></span>Realisasi</span>
                <span class="inline-flex items-center gap-3 text-white"><span class="h-2 w-10 rounded-full bg-red-500"></span>Terlambat</span>
            </div>
        </div>

        @if($alerts->isNotEmpty())
            <div class="mt-4 rounded-md border border-red-500/30 bg-red-500/10 p-3">
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
            <div class="mt-8 grid grid-cols-[210px_140px_220px_150px] gap-4 items-end text-sm">
                <div class="text-gray-400">Milestone</div>
                <div></div>
                <div class="relative h-8 text-gray-300">
                    @foreach($markers as $marker)
                        <span class="absolute -translate-x-1/2 whitespace-nowrap" style="left: {{ $marker['left'] }}%;">
                            {{ $marker['label'] }}
                        </span>
                    @endforeach
                </div>
                <div></div>
            </div>

            <div class="mt-3 divide-y divide-white/20 border-t border-white/70">
                @foreach($items as $item)
                    @php
                        $plannedStart = \Carbon\Carbon::parse($item['planned_start']);
                        $plannedEnd = \Carbon\Carbon::parse($item['planned_end']);
                        $actualStart = $item['actual_start'] ? \Carbon\Carbon::parse($item['actual_start']) : null;
                        $actualEnd = $item['actual_end'] ? \Carbon\Carbon::parse($item['actual_end']) : null;
                        $dayDiff = $actualEnd ? (int) $actualEnd->diffInDays($plannedEnd, false) : null;
                        $hasDelayRow = $item['is_delayed'] && $actualEnd;
                        $chartHeight = $hasDelayRow ? 'h-[86px]' : 'h-[62px]';
                        $resultText = 'Belum selesai';
                        $resultSubText = $item['status_label'];
                        $resultColor = 'text-gray-300';
                        $delayBar = null;

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
                                $delayBar = $barPosition($plannedEnd, $actualEnd);
                            }
                        } elseif ($item['is_delayed']) {
                            $resultText = $item['delay_days'] . ' hari terlambat';
                            $resultSubText = 'Belum selesai';
                            $resultColor = 'text-red-300';
                        }
                    @endphp

                    <div class="grid grid-cols-[210px_140px_220px_150px] gap-4 py-4 items-center">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $item['title'] }}</p>
                            <p class="mt-2 text-sm text-gray-400">{{ $item['division'] }} - {{ $item['status_label'] }}</p>
                        </div>

                        <div class="space-y-3 text-xs font-semibold">
                            <p><span class="text-white">Rencana</span> : {{ $formatCompactRange($item['planned_start'], $item['planned_end']) }}</p>
                            <p><span class="text-white">Realisasi</span> : {{ $formatCompactRange($item['actual_start'], $item['actual_end']) }}</p>
                            @if($hasDelayRow)
                                <p><span class="text-white">Terlambat</span> : {{ $formatCompactRange($item['planned_end'], $item['actual_end']) }}</p>
                            @endif
                        </div>

                        <div class="relative {{ $chartHeight }}">
                            @foreach($markers as $marker)
                                <div class="absolute top-0 bottom-0 w-px bg-white/5" style="left: {{ $marker['left'] }}%;"></div>
                            @endforeach

                            <div class="absolute left-0 right-0 top-1 h-4 rounded-full bg-white/10"></div>
                            <div class="absolute top-1 h-4 rounded-full bg-blue-500"
                                 style="left: {{ $item['planned']['left'] }}%; width: {{ $item['planned']['width'] }}%;"></div>
                            <div class="absolute top-0 h-6 w-2 rounded-full border border-blue-300"
                                 style="left: calc({{ $item['planned']['left'] }}% - 4px);"></div>
                            <div class="absolute top-0 h-6 w-2 rounded-full border border-blue-300"
                                 style="left: calc({{ $item['planned']['left'] + $item['planned']['width'] }}% - 4px);"></div>

                            <div class="absolute left-0 right-0 top-[31px] h-4 rounded-full bg-white/10"></div>
                            @if($item['actual'])
                                <div class="absolute top-[31px] h-4 rounded-full bg-green-500"
                                     style="left: {{ $item['actual']['left'] }}%; width: {{ $item['actual']['width'] }}%;"></div>
                                <div class="absolute top-[30px] h-6 w-2 rounded-full border border-green-300"
                                     style="left: calc({{ $item['actual']['left'] }}% - 4px);"></div>
                                <div class="absolute top-[30px] h-6 w-2 rounded-full border border-green-300"
                                     style="left: calc({{ $item['actual']['left'] + $item['actual']['width'] }}% - 4px);"></div>
                            @endif

                            @if($hasDelayRow && $delayBar)
                                <div class="absolute left-0 right-0 top-[61px] h-4 rounded-full bg-white/10"></div>
                                <div class="absolute top-[61px] h-4 rounded-full bg-red-500"
                                     style="left: {{ $delayBar['left'] }}%; width: {{ $delayBar['width'] }}%;"></div>
                                <div class="absolute top-[60px] h-6 w-2 rounded-full border border-red-300"
                                     style="left: calc({{ $delayBar['left'] }}% - 4px);"></div>
                                <div class="absolute top-[60px] h-6 w-2 rounded-full border border-red-300"
                                     style="left: calc({{ $delayBar['left'] + $delayBar['width'] }}% - 4px);"></div>
                            @endif
                        </div>

                        <div class="text-right self-end pb-1">
                            <p class="text-base font-bold {{ $resultColor }}">{{ $resultText }}</p>
                            <p class="mt-3 text-sm text-gray-400">{{ $resultSubText }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-gray-400">
                Timeline akan muncul setelah admin membuat task untuk divisi proyek.
            </div>
        @endif
    </div>
</div>
