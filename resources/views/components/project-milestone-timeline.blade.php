@php
    $items = collect($timelineData['items'] ?? []);
    $alerts = collect($timelineData['alerts'] ?? []);
    $months = collect($timelineData['months'] ?? []);
@endphp

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <div class="p-5 border-b border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-white">Timeline Milestone Rencana vs Realisasi</h3>
            <p class="text-sm text-gray-400">
                Dibentuk otomatis dari task setiap divisi, mulai dari awal proyek sampai deadline.
            </p>
        </div>
        <div class="flex flex-wrap gap-3 text-xs">
            <span class="inline-flex items-center gap-2 text-gray-300"><span class="w-3 h-3 rounded-sm bg-blue-500"></span>Rencana</span>
            <span class="inline-flex items-center gap-2 text-gray-300"><span class="w-3 h-3 rounded-sm bg-green-500"></span>Realisasi</span>
            <span class="inline-flex items-center gap-2 text-gray-300"><span class="w-3 h-3 rounded-sm bg-red-500"></span>Terlambat</span>
        </div>
    </div>

    @if($alerts->isNotEmpty())
        <div class="m-5 rounded-lg border border-red-500/30 bg-red-500/10 p-4">
            <p class="font-semibold text-red-300 mb-3">Pemberitahuan keterlambatan</p>
            <div class="space-y-2">
                @foreach($alerts as $alert)
                    <div class="text-sm text-red-100 bg-red-950/40 border border-red-500/20 rounded-md p-3">
                        <span class="font-semibold">{{ $alert['division'] }}</span> terlambat pada task
                        <span class="font-semibold">"{{ $alert['task'] }}"</span>
                        selama {{ $alert['delay_days'] }} hari.
                        <span class="text-red-200">Target: {{ $alert['planned_date'] }}</span>
                        @if($alert['actual_date'])
                            <span class="text-red-200">| Realisasi: {{ $alert['actual_date'] }}</span>
                        @else
                            <span class="text-red-200">| Status: {{ $alert['status'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($items->isNotEmpty())
        <div class="p-5 overflow-x-auto">
            <div class="min-w-[980px]">
                <div class="relative h-14 border-b border-gray-700 mb-5">
                    <div class="absolute left-0 right-0 bottom-0 h-2 bg-gray-700/80 rounded-full"></div>
                    @foreach($months as $month)
                        <div class="absolute bottom-0 -translate-x-1/2" style="left: {{ $month['left'] }}%;">
                            <div class="h-5 w-px bg-gray-500 mx-auto"></div>
                            <div class="mt-1 px-2 py-1 bg-gray-700 text-gray-200 text-xs rounded whitespace-nowrap">
                                {{ $month['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-4">
                    @foreach($items as $item)
                        <div class="grid grid-cols-[220px_1fr] gap-4 items-stretch rounded-lg border border-gray-700/80 bg-gray-900/25 p-4">
                            <div class="pt-1">
                                <p class="text-sm font-semibold text-white truncate">{{ $item['title'] }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ strtoupper($item['division']) }}</p>
                                @if($item['assignee'])
                                    <p class="text-[11px] text-gray-500 truncate">{{ $item['assignee'] }}</p>
                                @endif
                                <div class="mt-3">
                                    @if($item['is_delayed'])
                                        <span class="inline-flex px-2 py-1 rounded-full bg-red-900/40 text-red-200 border border-red-500/30 text-[11px] font-semibold">
                                            Terlambat {{ $item['delay_days'] }} hari
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full bg-green-900/30 text-green-200 border border-green-500/25 text-[11px] font-semibold">
                                            {{ $item['status_label'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="relative h-16 rounded-lg bg-gray-950/40 border border-gray-700 overflow-hidden">
                                    <div class="absolute left-0 right-0 top-1/2 h-px bg-gray-700/70"></div>
                                    <div class="absolute top-4 h-3 rounded-full bg-blue-500 shadow-sm shadow-blue-500/20"
                                         style="left: {{ $item['planned']['left'] }}%; width: {{ $item['planned']['width'] }}%;"></div>

                                    @if($item['actual'])
                                        <div class="absolute bottom-4 h-3 rounded-full {{ $item['is_delayed'] ? 'bg-red-500 shadow-sm shadow-red-500/20' : 'bg-green-500 shadow-sm shadow-green-500/20' }}"
                                             style="left: {{ $item['actual']['left'] }}%; width: {{ $item['actual']['width'] }}%;"></div>
                                    @endif

                                    <div class="absolute top-[13px] w-4 h-4 rounded-full bg-blue-500 ring-4 ring-blue-500/15"
                                         style="left: calc({{ $item['planned']['left'] }}% - 8px);"></div>
                                    @if($item['is_delayed'])
                                        <div class="absolute bottom-[13px] w-4 h-4 rounded-full bg-red-500 ring-4 ring-red-500/20"
                                             style="left: calc({{ $item['actual']['left'] + $item['actual']['width'] }}% - 8px);"></div>
                                    @elseif($item['actual'])
                                        <div class="absolute bottom-[13px] w-4 h-4 rounded-full bg-green-500 ring-4 ring-green-500/15"
                                             style="left: calc({{ $item['actual']['left'] + $item['actual']['width'] }}% - 8px);"></div>
                                    @endif
                                </div>

                                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1 text-[11px] text-gray-400">
                                    <span><span class="text-blue-300">Rencana:</span> {{ $item['planned_start'] }} - {{ $item['planned_end'] }}</span>
                                    @if($item['actual_start'])
                                        <span><span class="text-green-300">Realisasi:</span> {{ $item['actual_start'] }} - {{ $item['actual_end'] ?? 'Berjalan' }}</span>
                                    @else
                                        <span><span class="text-green-300">Realisasi:</span> belum dimulai</span>
                                    @endif
                                </div>
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
