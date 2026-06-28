@extends('layouts.app')

@section('title', 'Tambah Penawaran - Marketing')

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            ➕ Tambah Penawaran Baru
        </h1>
        <p class="text-gray-600 dark:text-gray-400">Isi formulir di bawah untuk mencatat penawaran</p>
    </div>

    {{-- FORM --}}
    <form action="{{ route('marketing.store') }}" method="POST" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-6">
        @csrf

        {{-- Informasi Perusahaan --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2">🏢 Informasi Perusahaan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Perusahaan *</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    @error('company_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bidang *</label>
                    <select name="category" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih Bidang</option>
                        <option value="web" {{ old('category') == 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                        <option value="internet" {{ old('category') == 'internet' ? 'selected' : '' }}>Layanan Internet</option>
                        <option value="cctv" {{ old('category') == 'cctv' ? 'selected' : '' }}>CCTV</option>
                    </select>
                    @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Perusahaan *</label>
                    <textarea name="company_address" rows="2" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('company_address') }}</textarea>
                    @error('company_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email / Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="08xx-xxxx-xxxx"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>

        {{-- Detail Penawaran --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2">📝 Detail Penawaran</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Penawaran *</label>
                    <input type="date" name="offer_date" value="{{ old('offer_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estimasi Nilai (Rp)</label>
                    <input type="number" name="estimated_value" value="{{ old('estimated_value') }}" min="0" step="1000"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Penawaran</label>
                    <textarea name="offer_description" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('offer_description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Status & Timeline --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2">📊 Status & Timeline</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="penawaran" {{ old('status') == 'penawaran' ? 'selected' : '' }}>Penawaran Dikirim</option>
                        <option value="follow_up" {{ old('status') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                        <option value="meeting" {{ old('status') == 'meeting' ? 'selected' : '' }}>Meeting Dijadwalkan</option>
                        <option value="menunggu_keputusan" {{ old('status') == 'menunggu_keputusan' ? 'selected' : '' }}>Menunggu Keputusan</option>
                        <option value="negosiasi" {{ old('status') == 'negosiasi' ? 'selected' : '' }}>Dalam Negosiasi</option>
                        <option value="deal" {{ old('status') == 'deal' ? 'selected' : '' }}>Deal / Closing</option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="no_response" {{ old('status') == 'no_response' ? 'selected' : '' }}>No Response</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Follow Up Date</label>
                    <input type="date" name="follow_up_date" value="{{ old('follow_up_date') }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meeting Date</label>
                    <input type="datetime-local" name="meeting_date" value="{{ old('meeting_date') }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan (jika rejected/pending)</label>
                    <textarea name="reason" rows="2" placeholder="Contoh: Budget tidak sesuai, menunggu approval, dll." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('reason') }}</textarea>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- BUTTONS --}}
        <div class="flex gap-3 pt-4 border-t">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                💾 Simpan Penawaran
            </button>
            <a href="{{ route('marketing.index') }}" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
