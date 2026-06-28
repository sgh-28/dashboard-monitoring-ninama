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
        <div class="flex gap-3 text-xs">
            <span class="inline-flex items-center gap-2 text-gray-300"><span class="w-3 h-3 rounded-sm bg-gray-500"></span>Rencana</span>
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
            <div class="min-w-[900px]">
                <div class="relative h-12 border-b border-gray-700 mb-4">
                    <div class="absolute left-0 right-0 bottom-0 h-2 bg-gray-700 rounded"></div>
                    @foreach($months as $month)
                        <div class="absolute bottom-0 -translate-x-1/2" style="left: {{ $month['left'] }}%;">
                            <div class="h-5 w-px bg-gray-500 mx-auto"></div>
                            <div class="mt-1 px-2 py-1 bg-gray-700 text-gray-200 text-xs rounded whitespace-nowrap">
                                {{ $month['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-5">
                    @foreach($items as $item)
                        <div class="grid grid-cols-[220px_1fr] gap-4 items-start">
                            <div class="pt-1">
                                <p class="text-sm font-semibold text-white truncate">{{ $item['title'] }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ strtoupper($item['division']) }}</p>
                                @if($item['assignee'])
                                    <p class="text-[11px] text-gray-500 truncate">{{ $item['assignee'] }}</p>
                                @endif
                            </div>

                            <div>
                                <div class="relative h-11 rounded-md bg-gray-900/60 border border-gray-700">
                                    <div class="absolute top-2 h-3 rounded-sm"
                                         style="left: {{ $item['planned']['left'] }}%; width: {{ $item['planned']['width'] }}%; background-color: {{ $item['color'] }}; opacity: .85;"></div>

                                    @if($item['actual'])
                                        <div class="absolute bottom-2 h-3 rounded-sm {{ $item['is_delayed'] ? 'bg-red-500' : 'bg-green-500' }}"
                                             style="left: {{ $item['actual']['left'] }}%; width: {{ $item['actual']['width'] }}%;"></div>
                                    @endif

                                    @if($item['is_delayed'])
                                        <div class="absolute -top-1 w-3 h-3 rounded-full bg-red-500 ring-4 ring-red-500/20"
                                             style="left: calc({{ $item['planned']['left'] + $item['planned']['width'] }}% - 6px);"></div>
                                    @endif
                                </div>

                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-400">
                                    <span>Rencana: {{ $item['planned_start'] }} - {{ $item['planned_end'] }}</span>
                                    @if($item['actual_start'])
                                        <span>Realisasi: {{ $item['actual_start'] }} - {{ $item['actual_end'] ?? 'Berjalan' }}</span>
                                    @else
                                        <span>Realisasi: belum dimulai</span>
                                    @endif
                                    @if($item['is_delayed'])
                                        <span class="text-red-300 font-semibold">Terlambat {{ $item['delay_days'] }} hari</span>
                                    @else
                                        <span class="text-green-300">{{ $item['status_label'] }}</span>
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
