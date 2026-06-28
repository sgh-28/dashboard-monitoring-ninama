@extends('layouts.app')

@section('title', 'Tambah Penawaran')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('marketing.index') }}" class="text-blue-400 hover:underline text-sm">Kembali ke Penawaran</a>
        <h1 class="text-2xl font-bold text-white mt-2">Tambah Penawaran</h1>
        <p class="text-gray-400 text-sm">Laporkan aktivitas penawaran untuk calon customer.</p>
    </div>

    <form action="{{ route('marketing.store') }}" method="POST" class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-6">
        @csrf

        <div>
            <h2 class="text-lg font-semibold text-white mb-4">Calon Customer</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nama Perusahaan / Instansi *</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('company_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nomor Kontak *</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" required placeholder="08xx..." class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('contact_phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nama Orang yang Ditawari *</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('contact_person') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Jabatan Orang yang Ditawari *</label>
                    <input type="text" name="contact_position" value="{{ old('contact_position') }}" required placeholder="Owner, Manager IT, Direktur, dll." class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('contact_position') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('contact_email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Alamat Calon Customer *</label>
                    <textarea name="company_address" rows="3" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">{{ old('company_address') }}</textarea>
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
                        <option value="">Pilih Bidang</option>
                        <option value="web" {{ old('category') === 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                        <option value="internet" {{ old('category') === 'internet' ? 'selected' : '' }}>Internet & Jaringan</option>
                        <option value="cctv" {{ old('category') === 'cctv' ? 'selected' : '' }}>CCTV</option>
                    </select>
                    @error('category') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Tanggal Penawaran *</label>
                    <input type="date" name="offer_date" value="{{ old('offer_date', now()->toDateString()) }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    @error('offer_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Estimasi Nilai (Rp)</label>
                    <input type="number" name="estimated_value" value="{{ old('estimated_value') }}" min="0" step="1000" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Daftar / Detail Penawaran yang Ditawarkan</label>
                    <textarea name="offer_description" rows="4" placeholder="Contoh: website company profile, paket internet kantor, instalasi CCTV 8 titik..." class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">{{ old('offer_description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-700">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">Simpan Penawaran</button>
            <a href="{{ route('marketing.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-medium">Batal</a>
        </div>
    </form>
</div>
@endsection
