@php
    $items = collect($timelineData['items'] ?? []);
    $alerts = collect($timelineData['alerts'] ?? []);
    $months = collect($timelineData['months'] ?? []);
@endphp

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <div class="p-5 border-b border-gray-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-white">Timeline Milestone Rencana vs Realisasi</h3>
            <p class="text-sm text-gray-400">
                Dibentuk otomatis dari task setiap divisi, mulai dari awal proyek sampai deadline.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-3 py-1 text-blue-200">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>Rencana
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-green-500/30 bg-green-500/10 px-3 py-1 text-green-200">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>Realisasi
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-red-500/30 bg-red-500/10 px-3 py-1 text-red-200">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>Terlambat
            </span>
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
            <div class="min-w-[1120px]">
                <div class="grid grid-cols-[240px_1fr] gap-5 text-xs text-gray-400 mb-3 px-1">
                    <div>Milestone</div>
                    <div class="flex items-center justify-between">
                        <span>Skala waktu proyek</span>
                        <span>{{ $items->count() }} milestone</span>
                    </div>
                </div>

                <div class="relative h-12 border border-gray-700 bg-gray-900/35 rounded-lg mb-4">
                    <div class="absolute left-4 right-4 top-1/2 h-1 bg-gray-700/80 rounded-full"></div>
                    @foreach($months as $month)
                        <div class="absolute top-2 -translate-x-1/2" style="left: {{ $month['left'] }}%;">
                            <div class="h-5 w-px bg-gray-500/80 mx-auto"></div>
                            <div class="mt-1 px-2 py-0.5 bg-gray-800 border border-gray-600 text-gray-200 text-[11px] rounded whitespace-nowrap">
                                {{ $month['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-3">
                    @foreach($items as $item)
                        <div class="grid grid-cols-[240px_1fr] gap-5 items-stretch rounded-lg border border-gray-700 bg-gray-900/30 p-4 hover:border-blue-500/40 transition">
                            <div class="flex flex-col justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-white leading-snug">{{ $item['title'] }}</p>
                                    <p class="mt-1 text-xs text-blue-200/90 uppercase tracking-wide">{{ strtoupper($item['division']) }}</p>
                                </div>

                                <div class="space-y-2">
                                    @if($item['assignee'])
                                        <p class="text-[11px] text-gray-400 truncate">{{ $item['assignee'] }}</p>
                                    @endif

                                    @if($item['is_delayed'])
                                        <span class="inline-flex px-2.5 py-1 rounded-full bg-red-900/40 text-red-200 border border-red-500/30 text-[11px] font-semibold">
                                            Terlambat {{ $item['delay_days'] }} hari
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-full bg-green-900/30 text-green-200 border border-green-500/25 text-[11px] font-semibold">
                                            {{ $item['status_label'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="relative h-24 rounded-lg bg-gray-950/35 border border-gray-700 overflow-hidden">
                                    <div class="absolute left-0 right-0 top-[34px] h-px bg-gray-700/80"></div>
                                    <div class="absolute left-0 right-0 top-[66px] h-px bg-gray-700/80"></div>

                                    <span class="absolute left-3 top-[17px] text-[10px] text-blue-200/80">Rencana</span>
                                    <span class="absolute left-3 top-[49px] text-[10px] text-green-200/80">Realisasi</span>

                                    <div class="absolute top-[28px] h-3 min-w-[0.75rem] rounded-full bg-blue-500 shadow-sm shadow-blue-500/20"
                                         style="left: {{ $item['planned']['left'] }}%; width: {{ $item['planned']['width'] }}%;"></div>

                                    @if($item['actual'])
                                        <div class="absolute top-[60px] h-3 min-w-[0.75rem] rounded-full {{ $item['is_delayed'] ? 'bg-red-500 shadow-sm shadow-red-500/20' : 'bg-green-500 shadow-sm shadow-green-500/20' }}"
                                             style="left: {{ $item['actual']['left'] }}%; width: {{ $item['actual']['width'] }}%;"></div>
                                    @else
                                        <span class="absolute top-[55px] left-[84px] text-[11px] text-gray-500">Belum dimulai</span>
                                    @endif

                                    <div class="absolute top-[24px] w-6 h-6 rounded-full bg-blue-500 ring-4 ring-gray-900 border-2 border-blue-100"
                                         style="left: calc({{ $item['planned']['left'] }}% - 12px);"></div>

                                    @if($item['is_delayed'])
                                        <div class="absolute top-[56px] w-6 h-6 rounded-full bg-red-500 ring-4 ring-gray-900 border-2 border-red-100"
                                             style="left: calc({{ $item['actual']['left'] + $item['actual']['width'] }}% - 12px);"></div>
                                    @elseif($item['actual'])
                                        <div class="absolute top-[56px] w-6 h-6 rounded-full bg-green-500 ring-4 ring-gray-900 border-2 border-green-100"
                                             style="left: calc({{ $item['actual']['left'] + $item['actual']['width'] }}% - 12px);"></div>
                                    @endif
                                </div>

                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2 text-[11px]">
                                    <span class="rounded-md border border-blue-500/20 bg-blue-500/10 px-3 py-2 text-blue-100">
                                        <span class="text-blue-300">Rencana:</span> {{ $item['planned_start'] }} - {{ $item['planned_end'] }}
                                    </span>
                                    @if($item['actual_start'])
                                        <span class="rounded-md border {{ $item['is_delayed'] ? 'border-red-500/20 bg-red-500/10 text-red-100' : 'border-green-500/20 bg-green-500/10 text-green-100' }} px-3 py-2">
                                            <span class="{{ $item['is_delayed'] ? 'text-red-300' : 'text-green-300' }}">Realisasi:</span> {{ $item['actual_start'] }} - {{ $item['actual_end'] ?? 'Berjalan' }}
                                        </span>
                                    @else
                                        <span class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-gray-400">
                                            <span class="text-gray-300">Realisasi:</span> belum dimulai
                                        </span>
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
