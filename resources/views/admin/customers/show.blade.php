@extends('layouts.app')

@section('title', 'Detail Customer')

@section('content')
<div class="p-6">
    <a href="{{ route('admin.customers.index') }}" class="text-blue-400 hover:underline text-sm">Kembali ke Kelola Customer</a>

    <div class="mt-4 bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $customer->company ?? $customer->name }}</h1>
                <p class="text-gray-400">{{ $customer->name }} · {{ $customer->email }}</p>
                <p class="text-gray-500 text-sm mt-1">{{ $customer->phone ?? 'Nomor kontak belum tersedia' }}</p>
            </div>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 rounded text-white">Edit Customer</a>
        </div>
    </div>

    <div class="mt-6 bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-xl font-semibold text-white">Proyek Customer</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-700 text-gray-300">
                <tr>
                    <th class="text-left px-4 py-3">Nama Proyek</th>
                    <th class="text-left px-4 py-3">Bidang</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Progress</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($customer->customerProjects as $project)
                    <tr>
                        <td class="px-4 py-4 text-white font-medium">{{ $project->name }}</td>
                        <td class="px-4 py-4 text-gray-300">{{ ucfirst($project->category) }}</td>
                        <td class="px-4 py-4 text-gray-300">{{ ucfirst($project->status) }}</td>
                        <td class="px-4 py-4 text-gray-300">{{ \App\Models\ProjectTask::formatSlaPercentage($project->overall_progress) }}</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.projects.show', $project) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Customer belum memiliki proyek.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
