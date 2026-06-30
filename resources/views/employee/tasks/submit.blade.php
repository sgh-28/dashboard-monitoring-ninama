@extends('layouts.app')

@section('title', 'Selesaikan Tugas')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employee.tasks.show', $task) }}" class="text-blue-400 hover:underline text-sm">← Kembali ke Detail Tugas</a>
        <h1 class="text-2xl font-bold text-white mt-2">📤 Selesaikan Tugas</h1>
        <p class="text-gray-400 text-sm">Laporkan hasil pengerjaan tugas Anda</p>
    </div>

    {{-- INFO TASK --}}
    <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-4 mb-6">
        <h3 class="text-white font-semibold mb-1">{{ $task->title }}</h3>
        <p class="text-sm text-gray-300">Proyek: {{ $task->project->name ?? '-' }}</p>
        @if($task->deadline)
            <p class="text-sm text-gray-400 mt-1">
                Deadline: {{ \Carbon\Carbon::parse($task->deadline)->format('d F Y') }}
            </p>
        @endif
    </div>

    {{-- FORM SUBMIT --}}
    <form action="{{ route('employee.tasks.submit', $task) }}" method="POST" enctype="multipart/form-data" 
          class="bg-gray-800 p-6 rounded-lg border border-gray-700 space-y-4">
        @csrf

        {{-- KETERANGAN (WAJIB) --}}
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">
                Keterangan Pengerjaan <span class="text-red-500">*</span>
            </label>
            <textarea name="completion_notes" rows="5" required 
                      class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                      placeholder="Jelaskan apa yang telah Anda kerjakan, hasil yang dicapai, atau kendala yang dihadapi...">{{ old('completion_notes') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Wajib diisi. Minimal jelaskan hasil pekerjaan Anda.</p>
            @error('completion_notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- BUKTI FOTO (WAJIB) --}}
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">
                Bukti Pengerjaan (Foto) <span class="text-red-500">*</span>
            </label>
            <input type="file" name="proof_image" accept="image/*" required 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700">
            <p class="text-xs text-gray-300 mt-1">
                Ukuran maksimal foto <span class="font-semibold text-yellow-300">5 MB</span>. Format yang diterima: JPG, JPEG, PNG.
                Jika lebih besar, kecilkan/kompres foto terlebih dahulu agar berhasil dikirim.
            </p>
            <p id="file-size-warning" class="hidden text-xs text-red-400 mt-1"></p>
            @error('proof_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- PREVIEW FOTO --}}
        <div id="preview-container" style="display: none;" class="mt-4">
            <p class="text-sm text-gray-400 mb-2">Preview:</p>
            <img id="preview-image" src="" alt="Preview" class="max-w-xs rounded-lg border border-gray-700">
        </div>

        {{-- PERINGATAN --}}
        <div class="bg-yellow-900/20 border border-yellow-500/30 rounded-lg p-3">
            <p class="text-sm text-yellow-300">
                ⚠️ <strong>Perhatian:</strong> Setelah Anda mengirim laporan ini, status tugas akan berubah menjadi <strong>Selesai (Done)</strong> dan akan dilaporkan ke Admin & Direktur. Pastikan data yang Anda isi sudah benar.
            </p>
        </div>

        {{-- BUTTONS --}}
        <div class="flex gap-3 pt-4 border-t border-gray-700">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">
                📤 Kirim Laporan
            </button>
            <a href="{{ route('employee.tasks.show', $task) }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-medium">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
// Preview gambar sebelum upload
document.querySelector('input[name="proof_image"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const warning = document.getElementById('file-size-warning');
    warning.classList.add('hidden');
    warning.textContent = '';

    if (file) {
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            warning.textContent = `Ukuran file ${(file.size / 1024 / 1024).toFixed(2)} MB. Maksimal yang bisa dikirim adalah 5 MB.`;
            warning.classList.remove('hidden');
            e.target.value = '';
            document.getElementById('preview-container').style.display = 'none';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview-container').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
