@extends('layouts.app')

@section('title', 'Edit Penawaran')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('marketing.index') }}" class="text-blue-400 hover:underline text-sm">Kembali ke Penawaran</a>
        <h1 class="text-2xl font-bold text-white mt-2">Edit Penawaran</h1>
        <p class="text-gray-400 text-sm">{{ $offer->company_name }}</p>
    </div>

    <form action="{{ route('marketing.update', $offer) }}" method="POST" class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <h2 class="text-lg font-semibold text-white mb-4">Calon Customer</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nama Perusahaan / Instansi *</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $offer->company_name) }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('company_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nomor Kontak *</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $offer->contact_phone) }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('contact_phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nama Orang yang Ditawari *</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $offer->contact_person) }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('contact_person') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Jabatan Orang yang Ditawari *</label>
                    <input type="text" name="contact_position" value="{{ old('contact_position', $offer->contact_position) }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('contact_position') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $offer->contact_email) }}" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('contact_email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Alamat Calon Customer *</label>
                    <textarea name="company_address" rows="3" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">{{ old('company_address', $offer->company_address) }}</textarea>
                    @error('company_address') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-white mb-4">Detail Penawaran</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Bidang yang Ditawarkan *</label>
                    <select name="category" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                        <option value="web" {{ old('category', $offer->category) === 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                        <option value="internet" {{ old('category', $offer->category) === 'internet' ? 'selected' : '' }}>Internet & Jaringan</option>
                        <option value="cctv" {{ old('category', $offer->category) === 'cctv' ? 'selected' : '' }}>CCTV</option>
                    </select>
                    @error('category') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Tanggal Penawaran *</label>
                    <input type="date" name="offer_date" value="{{ old('offer_date', $offer->offer_date?->format('Y-m-d')) }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('offer_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Estimasi Nilai (Rp)</label>
                    <input type="number" name="estimated_value" value="{{ old('estimated_value', $offer->estimated_value) }}" min="0" step="1000" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Daftar / Detail Penawaran yang Ditawarkan</label>
                    <textarea name="offer_description" rows="4" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">{{ old('offer_description', $offer->offer_description) }}</textarea>
                </div>
            </div>
        </div>

        <div>
            <button type="button"
                    id="add-offer-update"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                <span class="text-lg leading-none">+</span>
                Update Penawaran
            </button>
        </div>

        <div id="offer-update-panel" class="{{ ($errors->has('status') || $errors->has('follow_up_date') || $errors->has('meeting_date') || $errors->has('reason') || $errors->has('notes')) ? '' : 'hidden' }} rounded-lg border border-gray-700 bg-gray-900/30 p-5">
            <h2 class="text-lg font-semibold text-white mb-4">Perubahan Status Penawaran</h2>
            <input type="hidden" name="has_status_update" id="has-status-update" value="{{ ($errors->has('status') || $errors->has('follow_up_date') || $errors->has('meeting_date') || $errors->has('reason') || $errors->has('notes')) ? '1' : '0' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Progress Penawaran *</label>
                    <select name="status" required data-update-field class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                        <option value="">Pilih progress terbaru</option>
                        <option value="penawaran" {{ old('status') === 'penawaran' ? 'selected' : '' }}>Penawaran</option>
                        <option value="follow_up" {{ old('status') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                        <option value="meeting" {{ old('status') === 'meeting' ? 'selected' : '' }}>Meeting</option>
                        <option value="menunggu_keputusan" {{ old('status') === 'menunggu_keputusan' ? 'selected' : '' }}>Menunggu Keputusan Customer</option>
                        <option value="negosiasi" {{ old('status') === 'negosiasi' ? 'selected' : '' }}>Negosiasi</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ old('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="no_response" {{ old('status') === 'no_response' ? 'selected' : '' }}>No Response</option>
                        <option value="deal" {{ old('status') === 'deal' ? 'selected' : '' }}>Deal</option>
                    </select>
                    @error('status') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Tanggal Follow Up</label>
                    <input type="date" name="follow_up_date" value="{{ old('follow_up_date') }}" data-update-field class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Jadwal Meeting</label>
                    <input type="datetime-local" name="meeting_date" value="{{ old('meeting_date') }}" data-update-field class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Alasan / Kendala</label>
                    <textarea name="reason" rows="2" data-update-field class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">{{ old('reason') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" data-update-field class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-white mb-4">Riwayat Update Penawaran</h2>

            @php
                $histories = $offer->histories ?? collect();
            @endphp

            @if($histories->isNotEmpty())
                <div class="space-y-3">
                    @foreach($histories->sortByDesc('created_at') as $history)
                        <div class="rounded-lg border border-gray-700 bg-gray-900/40 p-4">
                            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-white">{{ $history->status_label }}</p>
                                    <p class="text-xs text-gray-400">
                                        Tanggal follow up:
                                        {{ $history->follow_up_date?->format('d/m/Y') ?? '-' }}
                                    </p>
                                </div>
                                <div class="text-xs text-gray-500 md:text-right">
                                    <p>Diupdate: {{ $history->created_at?->format('d/m/Y H:i') }}</p>
                                    <p>{{ $history->changedBy?->name ?? 'Marketing' }}</p>
                                </div>
                            </div>
                            <div class="mt-3 rounded border border-gray-700 bg-gray-800 p-3">
                                <p class="text-xs text-gray-500 mb-1">Keterangan:</p>
                                <p class="text-sm text-gray-300 whitespace-pre-line">{{ $history->notes ?: '-' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border border-gray-700 bg-gray-900/40 p-4 text-sm text-gray-400">
                    Belum ada riwayat perubahan status.
                </div>
            @endif
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-700">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">Simpan Perubahan</button>
            <a href="{{ route('marketing.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-medium">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-offer-update');
    const updatePanel = document.getElementById('offer-update-panel');
    const statusUpdateFlag = document.getElementById('has-status-update');
    const updateFields = document.querySelectorAll('[data-update-field]');

    if (!addButton || !updatePanel || !statusUpdateFlag) {
        return;
    }

    function setUpdateFieldsEnabled(enabled) {
        updateFields.forEach(function (field) {
            field.disabled = !enabled;
        });
    }

    addButton.addEventListener('click', function () {
        updatePanel.classList.remove('hidden');
        statusUpdateFlag.value = '1';
        setUpdateFieldsEnabled(true);
        addButton.classList.add('hidden');
    });

    const panelIsOpen = !updatePanel.classList.contains('hidden');
    setUpdateFieldsEnabled(panelIsOpen);
    if (panelIsOpen) {
        statusUpdateFlag.value = '1';
        addButton.classList.add('hidden');
    }
});
</script>
@endpush
@endsection
