@extends('layouts.app')
@section('title', 'Analisis Penawaran & Rejected')
@section('content')
<div class="p-6">
    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline mb-4 inline-block">← Kembali</a>
    <h1 class="text-2xl font-bold mb-6">📊 Analisis Penawaran & Proyek Ditolak</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        @foreach(['web'=>'Web & Aplikasi','internet'=>'Layanan Internet','cctv'=>'CCTV'] as $k=>$l)
        <div class="bg-white dark:bg-gray-800 p-5 rounded border"><h3 class="font-bold mb-3">{{ $l }}</h3><div class="space-y-2 text-sm"><div class="flex justify-between"><span class="text-green-600">✅ Diterima</span><span class="font-bold">{{ $offerData[$k]['accepted'] }}</span></div><div class="flex justify-between"><span class="text-red-600">❌ Ditolak</span><span class="font-bold">{{ $offerData[$k]['rejected'] }}</span></div><div class="flex justify-between"><span class="text-yellow-600">⏳ Pending</span><span class="font-bold">{{ $offerData[$k]['pending'] }}</span></div></div></div>
        @endforeach
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border"><h3 class="font-semibold mb-4">📋 Daftar Proyek Ditolak</h3><div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 dark:bg-gray-700 text-left"><tr><th class="p-3">Proyek</th><th class="p-3">Customer</th><th class="p-3">Alasan</th><th class="p-3">Tanggal</th></tr></thead><tbody>@forelse($rejectedProjects as $r)<tr class="border-t"><td class="p-3 font-medium">{{ $r->name }}</td><td class="p-3">{{ $r->customer->company ?? '-' }}</td><td class="p-3 text-red-500">{{ $r->rejection_reason ?? '-' }}</td><td class="p-3">{{ $r->created_at->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="4" class="p-4 text-center text-gray-500">Tidak ada data rejected.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection