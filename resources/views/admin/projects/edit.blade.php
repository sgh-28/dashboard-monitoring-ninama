@extends('layouts.app')

@section('title', 'Edit Proyek: ' . $project->name)

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">✏️ Edit Proyek: {{ $project->name }}</h1>
        <p class="text-gray-400">Perbarui informasi proyek</p>
    </div>

    <form action="{{ route('admin.projects.update', $project) }}" method="POST" class="bg-gray-800 rounded-lg p-6 border border-gray-700 space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Nama Proyek *</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Kategori Bidang *</label>
                <select name="category" id="category-select" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $project->category) == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Status Proyek</label>
                <select name="status" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                    @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ old('status', $project->status) == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Customer / Client</label>
                <select name="customer_id" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $project->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Deadline</label>
                <input type="date" name="deadline" value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
            </div>
        </div>

        <!-- ✅ DIVISI DINAMIS (EDIT MODE) -->
        <div class="bg-gray-700/50 p-4 rounded-lg border border-gray-600">
            <h3 class="text-lg font-semibold text-white mb-2">Bagian / Divisi yang Dikerjakan</h3>
            <div id="divisions-container" class="grid grid-cols-2 md:grid-cols-3 gap-3"></div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-700">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">💾 Update Proyek</button>
            <a href="{{ route('admin.projects.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category-select');
    const divisionsContainer = document.getElementById('divisions-container');
    const existingDivisions = @json($projectDivisions);
    
    if (!categorySelect || !divisionsContainer) return;

    function loadDivisions(category) {
        divisionsContainer.innerHTML = '<p class="text-gray-400 text-sm col-span-full">Memuat divisi...</p>';
        
        fetch(`{{ route('admin.projects.divisions', '') }}/${category}`)
            .then(response => response.json())
            .then(divisions => {
                divisionsContainer.innerHTML = '';
                if (divisions.length === 0) {
                    divisionsContainer.innerHTML = '<p class="text-gray-500 text-sm col-span-full">Tidak ada divisi.</p>';
                    return;
                }
                
                divisions.forEach(div => {
                    const checked = existingDivisions.includes(div) ? 'checked' : '';
                    const html = `
                        <label class="flex items-center p-2 bg-gray-800 rounded border border-gray-600 hover:bg-gray-700 cursor-pointer">
                            <input type="checkbox" name="divisions[]" value="${div}" ${checked} class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-300">${div}</span>
                        </label>
                    `;
                    divisionsContainer.insertAdjacentHTML('beforeend', html);
                });
            })
            .catch(() => {
                divisionsContainer.innerHTML = '<p class="text-red-400 text-sm col-span-full">Gagal memuat.</p>';
            });
    }

    categorySelect.addEventListener('change', function() {
        loadDivisions(this.value);
    });

    if (categorySelect.value) loadDivisions(categorySelect.value);
});
</script>
@endpush
@endsection