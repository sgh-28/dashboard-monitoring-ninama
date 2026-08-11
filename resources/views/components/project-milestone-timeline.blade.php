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
    $markerStep = $totalDays > 70 ? 14 : 7;

    while ($cursor->lte($end)) {
        $markers[] = [
            'label' => $cursor->translatedFormat('j M'),
            'left' => min(100, max(0, ($start->diffInDays($cursor) / $totalDays) * 100)),
        ];

        $cursor->addDays($markerStep);
    }

    if (empty($markers)) {
        $markers[] = [
            'label' => $end->translatedFormat('j M'),
            'left' => 100,
        ];
    } elseif (end($markers)['left'] < 94) {
        $markers[] = [
            'label' => $end->translatedFormat('j M'),
            'left' => 100,
        ];
    } elseif (end($markers)['left'] < 100) {
        $markers[array_key_last($markers)] = [
            'label' => $end->translatedFormat('j M'),
            'left' => 100,
        ];
    }

    $formatRange = function (?string $startDate, ?string $endDate) {
        if (!$startDate) {
            return '-';
        }

        $from = \Carbon\Carbon::parse($startDate);
        $to = $endDate ? \Carbon\Carbon::parse($endDate) : null;

        if (!$to) {
            return $from->translatedFormat('j M') . ' - berjalan';
        }

        if ($from->isSameDay($to)) {
            return $from->translatedFormat('j M');
        }

        if ($from->isSameMonth($to) && $from->isSameYear($to)) {
            return $from->translatedFormat('j') . ' - ' . $to->translatedFormat('j M');
        }

        return $from->translatedFormat('j M') . ' - ' . $to->translatedFormat('j M');
    };

    $barPosition = function ($fromDate, $toDate) use ($start, $totalDays) {
        $from = \Carbon\Carbon::parse($fromDate)->startOfDay();
        $to = \Carbon\Carbon::parse($toDate)->startOfDay();

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        $left = min(100, max(0, ($start->diffInDays($from, false) / $totalDays) * 100));
        $width = max(2.5, ($from->diffInDays($to) / $totalDays) * 100);

        return [
            'left' => $left,
            'width' => min(100 - $left, max(2.5, $width)),
        ];
    };

    $timelinePeriod = $start->isSameMonth($end) && $start->isSameYear($end)
        ? $start->translatedFormat('F Y')
        : $start->translatedFormat('F') . '-' . $end->translatedFormat('F Y');
@endphp

<section
    class="w-full overflow-hidden rounded-lg border"
    style="background:#0f1727;border-color:#334155;color:#e8eef8;font-family:Inter, Arial, sans-serif;"
>
    <div class="flex flex-col gap-5 border-b px-6 py-5 lg:flex-row lg:items-start lg:justify-between" style="border-color:#334155;">
        <div>
            <h3 class="text-[21px] font-semibold leading-tight" style="color:#e8eef8;">
                Timeline Milestone &mdash; Rencana vs Realisasi
            </h3>
            <p class="mt-2 text-[14px] leading-relaxed" style="color:#9fb0c7;">
                Rencana dan realisasi dipisahkan agar durasi serta selisih penyelesaian dapat dibandingkan dengan cepat.
            </p>
        </div>

        <div class="flex flex-wrap gap-4 text-[14px] font-medium lg:justify-end">
            <span class="inline-flex items-center gap-2" style="color:#e8eef8;">
                <span class="h-2.5 w-10 rounded-full" style="background:#4f8cff;"></span>
                Rencana
            </span>
            <span class="inline-flex items-center gap-2" style="color:#e8eef8;">
                <span class="h-2.5 w-10 rounded-full" style="background:#22c77a;"></span>
                Realisasi
            </span>
            <span class="inline-flex items-center gap-2" style="color:#e8eef8;">
                <span class="h-2.5 w-10 rounded-full" style="background:#f05d67;"></span>
                Terlambat
            </span>
        </div>
    </div>

    @if($alerts->isNotEmpty())
        <div class="mx-6 mt-5 rounded-lg border px-4 py-3" style="border-color:rgba(240,93,103,.45);background:rgba(240,93,103,.08);">
            <p class="text-[15px] font-semibold" style="color:#ffd1d5;">Pemberitahuan keterlambatan</p>
            <div class="mt-2 grid gap-2 text-[13px]" style="color:#ffdce0;">
                @foreach($alerts as $alert)
                    <p>
                        <span class="font-semibold">{{ $alert['division'] }}</span> - {{ $alert['task'] }}
                        terlambat {{ $alert['delay_days'] }} hari.
                    </p>
                @endforeach
            </div>
        </div>
    @endif

    @if($items->isNotEmpty())
        <div class="overflow-x-auto px-6 py-6">
            <div class="min-w-[1040px] w-full">
                <div class="grid gap-4" style="grid-template-columns:minmax(210px,250px) minmax(210px,230px) minmax(360px,1fr) minmax(150px,170px);">
                    <div class="text-[14px]" style="color:#9fb0c7;">Milestone - {{ $timelinePeriod }}</div>
                    <div></div>
                    <div class="relative h-10">
                        <div class="absolute left-0 right-0 top-8 h-px" style="background:#43526a;"></div>
                        @foreach($markers as $marker)
                            <span
                                class="absolute top-0 -translate-x-1/2 whitespace-nowrap text-[14px]"
                                style="left:{{ $marker['left'] }}%;color:#9fb0c7;"
                            >
                                {{ $marker['label'] }}
                            </span>
                        @endforeach
                    </div>
                    <div></div>
                </div>

                <div class="mt-3 space-y-3">
                    @foreach($items as $item)
                        @php
                            $plannedStart = \Carbon\Carbon::parse($item['planned_start'])->startOfDay();
                            $plannedEnd = \Carbon\Carbon::parse($item['planned_end'])->startOfDay();
                            $actualStart = $item['actual_start'] ? \Carbon\Carbon::parse($item['actual_start'])->startOfDay() : null;
                            $actualEnd = $item['actual_end'] ? \Carbon\Carbon::parse($item['actual_end'])->startOfDay() : null;
                            $today = now()->startOfDay();

                            $plannedBar = $barPosition($plannedStart, $plannedEnd);
                            $actualBar = $actualStart ? $barPosition($actualStart, $actualEnd ?? $today) : null;
                            $lateBar = null;
                            $hasLateLane = false;

                            if ($item['is_delayed']) {
                                $lateFrom = $plannedEnd;
                                $lateTo = $actualEnd ?? $today;
                                $lateBar = $barPosition($lateFrom, $lateTo);
                                $hasLateLane = true;
                            }

                            $actualRowEnd = $actualEnd ?? null;
                            $diffText = 'Belum selesai';
                            $diffSubText = $item['status_label'];
                            $diffColor = '#9fb0c7';

                            if ($actualRowEnd) {
                                $diffDays = (int) $actualRowEnd->diffInDays($plannedEnd, false);

                                if ($diffDays > 0) {
                                    $diffText = $diffDays . ' hari lebih cepat';
                                    $diffSubText = 'Sesuai rencana';
                                    $diffColor = '#e8eef8';
                                } elseif ($diffDays === 0) {
                                    $diffText = 'Tepat waktu';
                                    $diffSubText = 'Sesuai rencana';
                                    $diffColor = '#e8eef8';
                                } else {
                                    $diffText = abs($diffDays) . ' hari terlambat';
                                    $diffSubText = 'Melewati rencana';
                                    $diffColor = '#f05d67';
                                }
                            } elseif ($item['is_delayed']) {
                                $diffText = $item['delay_days'] . ' hari terlambat';
                                $diffSubText = 'Belum selesai';
                                $diffColor = '#f05d67';
                            }

                            $laneHeight = $hasLateLane ? '94px' : '64px';
                        @endphp

                        <div class="grid gap-4 rounded-lg border px-5 py-5" style="grid-template-columns:minmax(210px,250px) minmax(210px,230px) minmax(360px,1fr) minmax(150px,170px);background:#172235;border-color:#334155;">
                            <div>
                                <p class="text-[18px] font-semibold leading-snug" style="color:#e8eef8;">{{ $item['title'] }}</p>
                                <p class="mt-2 text-[14px] leading-relaxed" style="color:#9fb0c7;">
                                    {{ $item['division'] }} &middot; {{ $item['status_label'] }}
                                </p>
                            </div>

                            <div class="space-y-3 pt-1 text-[14px] font-semibold leading-relaxed" style="color:#e8eef8;">
                                <p>Rencana : {{ $formatRange($item['planned_start'], $item['planned_end']) }}</p>
                                <p>Realisasi : {{ $formatRange($item['actual_start'], $item['actual_end']) }}</p>
                                @if($hasLateLane)
                                    <p style="color:#f05d67;">Terlambat : {{ $formatRange($item['planned_end'], $item['actual_end'] ?? now()->toDateString()) }}</p>
                                @endif
                            </div>

                            <div class="relative min-w-0 overflow-hidden" style="height:{{ $laneHeight }};">
                                @foreach($markers as $marker)
                                    <div
                                        class="absolute top-0 bottom-0 w-px"
                                        style="left:{{ $marker['left'] }}%;background:#2d3a4e;"
                                    ></div>
                                @endforeach

                                <div class="absolute left-0 right-0 rounded-full" style="top:4px;height:16px;background:#263348;"></div>
                                <div
                                    class="absolute rounded-full shadow-sm"
                                    style="top:4px;height:16px;left:{{ $plannedBar['left'] }}%;width:{{ $plannedBar['width'] }}%;background:#4f8cff;"
                                ></div>
                                <div class="absolute rounded-full border" style="top:2px;height:20px;width:8px;left:calc({{ $plannedBar['left'] }}% - 4px);border-color:#4f8cff;background:#172235;"></div>
                                <div class="absolute rounded-full border" style="top:2px;height:20px;width:8px;left:calc({{ $plannedBar['left'] + $plannedBar['width'] }}% - 4px);border-color:#4f8cff;background:#172235;"></div>

                                <div class="absolute left-0 right-0 rounded-full" style="top:36px;height:16px;background:#263348;"></div>
                                @if($actualBar)
                                    <div
                                        class="absolute rounded-full shadow-sm"
                                        style="top:36px;height:16px;left:{{ $actualBar['left'] }}%;width:{{ $actualBar['width'] }}%;background:#22c77a;"
                                    ></div>
                                    <div class="absolute rounded-full border" style="top:34px;height:20px;width:8px;left:calc({{ $actualBar['left'] }}% - 4px);border-color:#22c77a;background:#172235;"></div>
                                    <div class="absolute rounded-full border" style="top:34px;height:20px;width:8px;left:calc({{ $actualBar['left'] + $actualBar['width'] }}% - 4px);border-color:#22c77a;background:#172235;"></div>
                                @endif

                                @if($hasLateLane && $lateBar)
                                    <div class="absolute left-0 right-0 rounded-full" style="top:68px;height:16px;background:#263348;"></div>
                                    <div
                                        class="absolute rounded-full shadow-sm"
                                        style="top:68px;height:16px;left:{{ $lateBar['left'] }}%;width:{{ $lateBar['width'] }}%;background:#f05d67;"
                                    ></div>
                                    <div class="absolute rounded-full border" style="top:66px;height:20px;width:8px;left:calc({{ $lateBar['left'] }}% - 4px);border-color:#f05d67;background:#172235;"></div>
                                    <div class="absolute rounded-full border" style="top:66px;height:20px;width:8px;left:calc({{ $lateBar['left'] + $lateBar['width'] }}% - 4px);border-color:#f05d67;background:#172235;"></div>
                                @endif
                            </div>

                            <div class="self-center text-right">
                                <p class="text-[16px] font-semibold leading-snug" style="color:{{ $diffColor }};">{{ $diffText }}</p>
                                <p class="mt-2 text-[14px]" style="color:#9fb0c7;">{{ $diffSubText }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="px-6 py-10 text-center text-[14px]" style="color:#9fb0c7;">
            Timeline akan muncul setelah admin membuat task untuk divisi proyek.
        </div>
    @endif
</section>
