@extends('layouts.app')

@section('title', 'Dashboard Direktur - Ninama')

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-ninama.png') }}" 
                 alt="Logo Ninama" 
                 class="w-12 h-12 object-contain">
            
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Dashboard Direktur</h1>
                <p class="text-gray-400">Monitoring progres proyek lintas bidang (Web, Internet, CCTV)</p>
            </div>
        </div>
        
        <a href="{{ route('direktur.export.all') }}" 
           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition flex items-center gap-2 shadow-lg border border-emerald-500/30"
           title="Download Excel: Semua Data Proyek + Marketing (Multi-Sheet)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Laporan Lengkap
        </a>
    </div>

    {{-- STATISTIK PER KATEGORI DENGAN PIE CHART --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @php
            $categories = ['web' => 'Web & Aplikasi', 'internet' => 'Internet & Jaringan', 'cctv' => 'CCTV'];
            $currentCategory = request('category');
            
            // ✅ PERBAIKAN: Hitung statistik per kategori (Exclude 'rejected')
            $categoryStats = [];
            foreach($categories as $key => $label) {
                $total = \App\Models\Project::where('category', $key)->whereIn('status', ['ongoing', 'done'])->count();
                $ongoing = \App\Models\Project::where('category', $key)->where('status', 'ongoing')->count();
                $done = \App\Models\Project::where('category', $key)->where('status', 'done')->count();
                
                $categoryStats[$key] = [
                    'total' => $total,
                    'ongoing' => $ongoing,
                    'done' => $done,
                ];
            }
        @endphp

        @foreach($categories as $key => $label)
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 {{ $currentCategory === $key ? 'ring-2 ring-blue-500' : '' }}">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white">{{ $label }}</h3>
                <a href="{{ route('projects.export', ['category' => $key]) }}" 
                   class="px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded transition flex items-center gap-1"
                   title="Export Excel untuk {{ $label }} saja">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export
                </a>
            </div>
            
            {{-- ✅ PIE CHART CONTAINER --}}
            <div class="relative h-48 mb-4">
                <canvas id="chart-{{ $key }}"></canvas>
            </div>

            {{-- LEGEND & STATS --}}
            <div class="flex justify-between items-center mb-4 pt-4 border-t border-gray-700 text-xs">
                <div class="text-center flex-1">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        <span class="w-3 h-3 rounded-full" style="background-color: #3b82f6;"></span>
                        <span class="text-gray-400">Ongoing</span>
                    </div>
                    <p class="font-bold text-blue-400 text-sm">{{ $categoryStats[$key]['ongoing'] }}</p>
                </div>
                <div class="text-center flex-1">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        <span class="w-3 h-3 rounded-full" style="background-color: #22c55e;"></span>
                        <span class="text-gray-400">Selesai</span>
                    </div>
                    <p class="font-bold text-green-400 text-sm">{{ $categoryStats[$key]['done'] }}</p>
                </div>
            </div>

            {{-- TOTAL & PROGRESS BAR --}}
            @php
                $total = $categoryStats[$key]['total'];
                $done = $categoryStats[$key]['done'];
                $percent = $total > 0 ? round(($done / $total) * 100) : 0;
            @endphp
            <div class="pt-4 border-t border-gray-700">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-400 text-sm">Total Proyek</span>
                    <span class="font-bold text-white text-lg">{{ $total }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>Tingkat Penyelesaian</span>
                    <span>{{ $percent }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-400 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $percent }}%"></div>
                </div>
            </div>

            <div class="mt-4 pt-2 border-t border-gray-700/50">
                <a href="{{ route('projects.category.detail', ['category' => $key]) }}" 
                   class="text-sm text-blue-400 hover:underline flex items-center gap-1">
                    Klik untuk detail →
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- RECENT PROJECTS PREVIEW --}}
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Proyek Terbaru
                @if($currentCategory)
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-blue-900/50 text-blue-300 border border-blue-500/30">
                        {{ $categories[$currentCategory] ?? 'Semua Bidang' }}
                    </span>
                @endif
            </h3>
            <a href="{{ route('admin.projects.index') }}" class="text-sm text-blue-400 hover:underline">
                Lihat Semua →
            </a>
        </div>
        
        {{-- FILTER & PENCARIAN --}}
        <form method="GET" action="{{ route('direktur.dashboard') }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Cari Proyek</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Nama proyek atau customer..."
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Kategori</label>
                    <select name="category" 
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kategori</option>
                        <option value="web" {{ request('category') == 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                        <option value="internet" {{ request('category') == 'internet' ? 'selected' : '' }}>Internet & Jaringan</option>
                        <option value="cctv" {{ request('category') == 'cctv' ? 'selected' : '' }}>CCTV</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Status</label>
                    <select name="status" 
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('direktur.dashboard') }}" 
                       class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-700">
                        <th class="pb-3 font-medium">Nama Proyek</th>
                        <th class="pb-3 font-medium">Kategori</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Progress</th>
                        <th class="pb-3 font-medium">Deadline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($recentProjects ?? [] as $project)
                        @php
                            $deadline = $project->deadline ? \Carbon\Carbon::parse($project->deadline) : null;
                            $isOverdue = $deadline && $deadline->isPast() && $project->status !== 'done';
                            $daysOverdue = $isOverdue ? (int) $deadline->copy()->startOfDay()->diffInDays(now()->startOfDay()) : 0;
                        @endphp
                        
                        <tr class="hover:bg-gray-700/50 transition {{ $isOverdue ? 'bg-red-900/10 border-l-4 border-red-500' : '' }}">
                            <td class="py-3 pr-4">
                                <p class="font-medium text-white">{{ $project->name }}</p>
                                <p class="text-xs text-gray-500">{{ $project->client_name ?? '-' }}</p>
                            </td>
                            <td class="py-3 pr-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-900/50 text-blue-300 border border-blue-500/30">
                                    {{ ucfirst($project->category) }}
                                </span>
                            </td>
                            
                            <td class="py-3 pr-4">
                                @if($isOverdue)
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-900/50 text-red-300 border border-red-500/30 flex items-center gap-1 w-fit">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Overdue
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($project->status === 'done') bg-green-900/50 text-green-300
                                        @elseif($project->status === 'ongoing') bg-yellow-900/50 text-yellow-300
                                        @else bg-gray-700 text-gray-300 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                @endif
                            </td>
                            
                            <td class="py-3 pr-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full transition-all duration-500 
                                            @if($isOverdue) bg-red-500 animate-pulse
                                            @else bg-blue-500 @endif" 
                                             style="width: {{ $project->progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs @if($isOverdue) text-red-400 font-semibold @else text-gray-400 @endif">
                                        {{ $project->progress ?? 0 }}%
                                    </span>
                                </div>
                            </td>
                            
                            <td class="py-3">
                                @if($project->deadline)
                                    <div class="flex flex-col gap-1">
                                        <span class="text-gray-300 @if($isOverdue) text-red-400 font-semibold @endif">
                                            {{ $deadline->format('d/m/Y') }}
                                        </span>
                                        @if($isOverdue)
                                            <span class="text-xs text-red-500 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                +{{ $daysOverdue }} hari terlambat
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">
                                {{ $currentCategory ? 'Tidak ada proyek aktif untuk kategori ini.' : 'Belum ada data proyek aktif.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MARKETING REPORT PREVIEW --}}
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mt-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Laporan Marketing Terbaru</h3>
                <p class="text-sm text-gray-400">Terintegrasi dengan status penawaran marketing, project, divisi, dan task.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                <div class="bg-gray-700/60 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400">Total</p>
                    <p class="font-bold text-white">{{ $marketingStats['total'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-700/60 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400">Proses</p>
                    <p class="font-bold text-yellow-400">{{ $marketingStats['active'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-700/60 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400">Deal</p>
                    <p class="font-bold text-green-400">{{ $marketingStats['deal'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-700/60 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400">Perlu Akun</p>
                    <p class="font-bold text-amber-400">{{ $marketingStats['needs_account'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-700">
                        <th class="pb-3 font-medium">Calon Customer</th>
                        <th class="pb-3 font-medium">Bidang</th>
                        <th class="pb-3 font-medium">Penawaran</th>
                        <th class="pb-3 font-medium">Progress</th>
                        <th class="pb-3 font-medium">Project / Task</th>
                        <th class="pb-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($marketingOffers ?? [] as $offer)
                        @php
                            $statusClasses = [
                                'penawaran' => 'bg-blue-900/50 text-blue-300',
                                'follow_up' => 'bg-yellow-900/50 text-yellow-300',
                                'meeting' => 'bg-purple-900/50 text-purple-300',
                                'menunggu_keputusan' => 'bg-gray-700 text-gray-300',
                                'negosiasi' => 'bg-orange-900/50 text-orange-300',
                                'deal' => 'bg-green-900/50 text-green-300',
                                'pending' => 'bg-yellow-900/50 text-yellow-300',
                                'rejected' => 'bg-red-900/50 text-red-300',
                                'no_response' => 'bg-red-900/50 text-red-300',
                            ];
                            $needsAccount = $offer->status === 'deal' && !$offer->project_id;
                            $copyText = "DATA CUSTOMER DARI PENAWARAN MARKETING\n"
                                . "Nama Perusahaan: {$offer->company_name}\n"
                                . "Nama Customer/Kontak: " . ($offer->contact_person ?: '-') . "\n"
                                . "Jabatan: " . ($offer->contact_position ?: '-') . "\n"
                                . "Email: " . ($offer->contact_email ?: '-') . "\n"
                                . "Nomor HP: " . ($offer->contact_phone ?: '-') . "\n"
                                . "Alamat: {$offer->company_address}\n\n"
                                . "DATA PROJECT\n"
                                . "Nama Project: " . ($offer->offer_description ?: $offer->company_name) . "\n"
                                . "Bidang: " . ucfirst($offer->category) . "\n"
                                . "Nilai Penawaran: " . ($offer->estimated_value ? 'Rp ' . number_format((float) $offer->estimated_value, 0, ',', '.') : '-') . "\n"
                                . "Tanggal Deal/Penawaran: " . ($offer->offer_date ? $offer->offer_date->format('d/m/Y') : '-') . "\n"
                                . "Marketing: " . ($offer->employee?->name ?: '-') . "\n"
                                . "Catatan: " . ($offer->notes ?: '-');
                        @endphp
                        <tr class="hover:bg-gray-700/50 transition">
                            <td class="py-3 pr-4">
                                <p class="font-medium text-white">{{ $offer->company_name }}</p>
                                <p class="text-xs text-gray-400">{{ $offer->contact_person ?: '-' }}{{ $offer->contact_position ? ' - ' . $offer->contact_position : '' }}</p>
                                <p class="text-xs text-gray-500">{{ $offer->employee?->name ?: 'Marketing tidak tersedia' }}</p>
                            </td>
                            <td class="py-3 pr-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-900/50 text-blue-300 border border-blue-500/30">
                                    {{ ucfirst($offer->category) }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-gray-300 max-w-xs">
                                <p>{{ Str::limit($offer->offer_description ?: '-', 70) }}</p>
                                @if($offer->estimated_value)
                                    <p class="text-xs text-gray-500 mt-1">Rp {{ number_format((float) $offer->estimated_value, 0, ',', '.') }}</p>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $statusClasses[$offer->status] ?? 'bg-gray-700 text-gray-300' }}">
                                    {{ $offer->status_label }}
                                </span>
                                @if($needsAccount)
                                    <p class="mt-2 text-xs text-amber-300 bg-amber-900/30 border border-amber-500/30 rounded px-2 py-1 w-fit">
                                        Perlu akun customer
                                    </p>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                @if($offer->project)
                                    <p class="font-medium text-white">{{ $offer->project->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $offer->project->divisions_count }} divisi, {{ $offer->project->tasks_count }} task</p>
                                @else
                                    <span class="text-xs text-gray-500">Belum dibuat project</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                @if($needsAccount)
                                    <button type="button"
                                            class="copy-offer-btn px-3 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded transition"
                                            data-copy="{{ e($copyText) }}">
                                        Copy Data
                                    </button>
                                @else
                                    <span class="text-xs text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500">
                                Belum ada laporan marketing.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ✅ PIE CHART SCRIPT --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @foreach($categories as $key => $label)
        const ctx{{ ucfirst($key) }} = document.getElementById('chart-{{ $key }}').getContext('2d');
        new Chart(ctx{{ ucfirst($key) }}, {
            type: 'doughnut',
            data: {
                labels: ['Ongoing', 'Selesai'],
                datasets: [{
                    data: [
                        {{ $categoryStats[$key]['ongoing'] }},
                        {{ $categoryStats[$key]['done'] }}
                    ],
                    backgroundColor: [
                        '#3b82f6',
                        '#22c55e'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    @endforeach

    document.querySelectorAll('.copy-offer-btn').forEach(function (button) {
        button.addEventListener('click', async function () {
            const text = this.dataset.copy || '';
            const originalText = this.textContent;

            try {
                await navigator.clipboard.writeText(text);
            } catch (error) {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                textarea.remove();
            }

            this.textContent = 'Tersalin';
            setTimeout(() => this.textContent = originalText, 1500);
        });
    });
});
</script>
@endpush
@endsection
