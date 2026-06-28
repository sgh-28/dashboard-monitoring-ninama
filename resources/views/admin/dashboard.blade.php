@extends('layouts.app')

@section('title', 'Dashboard Utama - Ninama')

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
            📊 Dashboard Utama
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Monitor progres proyek lintas bidang (Web, Internet, CCTV)
        </p>
    </div>

    {{-- STATISTIK PER KATEGORI --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach(['web' => 'Web & Aplikasi', 'internet' => 'Internet & Jaringan', 'cctv' => 'CCTV'] as $key => $label)
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ $label }}</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total</span>
                    <span class="font-bold text-blue-600">{{ $categoryStats[$key]['total'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Ongoing</span>
                    <span class="font-bold text-yellow-600">{{ $categoryStats[$key]['ongoing'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Selesai</span>
                    <span class="font-bold text-green-600">{{ $categoryStats[$key]['done'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Offer</span>
                    <span class="font-bold text-purple-600">{{ $categoryStats[$key]['offer'] ?? 0 }}</span>
                </div>
            </div>
            
            <a href="{{ route('projects.category.detail', ['category' => $key]) }}" 
               class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                Lihat Detail →
            </a>
        </div>
        @endforeach
    </div>

    {{-- QUICK ACTIONS (Super Admin Only) --}}
    @if(Auth::user()?->role?->name === 'super_admin')
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Aksi Cepat</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.projects.create') }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                + Tambah Proyek
            </a>
            <a href="{{ route('admin.customers.create') }}" 
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                + Tambah Customer
            </a>
            <a href="{{ route('admin.offers') }}" 
               class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                📈 Analisis Penawaran
            </a>
        </div>
    </div>
    @endif
</div>
@endsection