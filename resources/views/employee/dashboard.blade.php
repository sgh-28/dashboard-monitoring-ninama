@extends('layouts.app')

@section('title', 'Dashboard Utama - Ninama')

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard Utama</h1>
            <p class="text-gray-400 text-sm">Selamat datang, {{ Auth::user()->name }}</p>
        </div>
        <a href="{{ route('admin.projects.create') }}" 
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Proyek
        </a>
    </div>

    {{-- STATISTIK PROYEK PER BIDANG --}}
    <h3 class="text-lg font-semibold text-white mb-4">📊 Statistik Proyek Per Bidang</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @php
            $categories = [
                'web' => ['name' => 'Web & Aplikasi', 'color' => 'blue'],
                'internet' => ['name' => 'Internet & Jaringan', 'color' => 'green'],
                'cctv' => ['name' => 'CCTV', 'color' => 'purple']
            ];

            // Hitung statistik per kategori
            $stats = [];
            foreach($categories as $key => $cat) {
                $total = \App\Models\Project::where('category', $key)->where('status', '!=', 'rejected')->count();
                $ongoing = \App\Models\Project::where('category', $key)->where('status', 'ongoing')->count();
                $done = \App\Models\Project::where('category', $key)->where('status', 'done')->count();
                $offer = \App\Models\Project::where('category', $key)->whereIn('status', ['offer', 'progress_offer', 'pending'])->count();
                
                $stats[$key] = [
                    'total' => $total,
                    'ongoing' => $ongoing,
                    'done' => $done,
                    'offer' => $offer,
                ];
            }
        @endphp

        @foreach($categories as $key => $cat)
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white">{{ $cat['name'] }}</h3>
                <span class="px-2 py-1 text-xs rounded-full bg-gray-700 text-gray-300">
                    Total: {{ $stats[$key]['total'] }}
                </span>
            </div>
            
            {{-- PIE CHART CONTAINER --}}
            <div class="relative h-48 mb-4">
                <canvas id="chart-{{ $key }}"></canvas>
            </div>

            {{-- LEGEND --}}
            <div class="grid grid-cols-3 gap-2 text-xs">
                <div class="text-center">
                    <div class="w-3 h-3 rounded-full bg-blue-500 inline-block mb-1"></div>
                    <p class="text-gray-400">Ongoing</p>
                    <p class="font-bold text-blue-400">{{ $stats[$key]['ongoing'] }}</p>
                </div>
                <div class="text-center">
                    <div class="w-3 h-3 rounded-full bg-green-500 inline-block mb-1"></div>
                    <p class="text-gray-400">Selesai</p>
                    <p class="font-bold text-green-400">{{ $stats[$key]['done'] }}</p>
                </div>
                <div class="text-center">
                    <div class="w-3 h-3 rounded-full bg-yellow-500 inline-block mb-1"></div>
                    <p class="text-gray-400">Pending/Offer</p>
                    <p class="font-bold text-yellow-400">{{ $stats[$key]['offer'] }}</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-700">
                <a href="{{ route('projects.category.detail', ['category' => $key]) }}" 
                   class="text-sm text-blue-400 hover:underline">
                    Klik untuk detail →
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- PROYEK TERBARU (NON-REJECTED) --}}
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white">Proyek Terbaru (Non-Rejected)</h3>
            <a href="{{ route('admin.projects.index') }}" class="text-sm text-blue-400 hover:underline">
                Lihat Semua →
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-700">
                        <th class="pb-3 font-medium">Nama Proyek</th>
                        <th class="pb-3 font-medium">Customer</th>
                        <th class="pb-3 font-medium">Bidang</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Deadline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @php
                        $recentProjects = \App\Models\Project::where('status', '!=', 'rejected')
                            ->with('customer')
                            ->orderByDesc('created_at')
                            ->limit(5)
                            ->get();
                    @endphp
                    @forelse($recentProjects as $project)
                    <tr class="hover:bg-gray-700/50 transition">
                        <td class="py-3 pr-4">
                            <p class="font-medium text-white">{{ $project->name }}</p>
                        </td>
                        <td class="py-3 pr-4 text-gray-300">
                            {{ $project->customer?->company ?? $project->client_name ?? '-' }}
                        </td>
                        <td class="py-3 pr-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-900/50 text-blue-300 border border-blue-500/30">
                                {{ ucfirst($project->category) }}
                            </span>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($project->status === 'done') bg-green-900/50 text-green-300
                                @elseif($project->status === 'ongoing') bg-blue-900/50 text-blue-300
                                @elseif(in_array($project->status, ['offer', 'progress_offer', 'pending'])) bg-yellow-900/50 text-yellow-300
                                @else bg-gray-700 text-gray-300 @endif">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($project->deadline)
                                <span class="text-gray-300">
                                    {{ \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500">
                            Belum ada data proyek.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- CHART.JS SCRIPT --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @foreach($categories as $key => $cat)
    const ctx{{ ucfirst($key) }} = document.getElementById('chart-{{ $key }}').getContext('2d');
    new Chart(ctx{{ ucfirst($key) }}, {
        type: 'doughnut',
        data: {
            labels: ['Ongoing', 'Selesai', 'Pending/Offer'],
            datasets: [{
                data: [
                    {{ $stats[$key]['ongoing'] }},
                    {{ $stats[$key]['done'] }},
                    {{ $stats[$key]['offer'] }}
                ],
                backgroundColor: [
                    '#3b82f6', // blue-500
                    '#22c55e', // green-500
                    '#eab308'  // yellow-500
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    @endforeach
});
</script>
@endpush
@endsection