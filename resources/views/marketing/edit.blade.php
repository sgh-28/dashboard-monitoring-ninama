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
            <h2 class="text-lg font-semibold text-white mb-4">Perubahan Status Penawaran</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Progress Penawaran *</label>
                    <select name="status" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                        <option value="penawaran" {{ old('status', $offer->status) === 'penawaran' ? 'selected' : '' }}>Penawaran</option>
                        <option value="follow_up" {{ old('status', $offer->status) === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                        <option value="meeting" {{ old('status', $offer->status) === 'meeting' ? 'selected' : '' }}>Meeting</option>
                        <option value="menunggu_keputusan" {{ old('status', $offer->status) === 'menunggu_keputusan' ? 'selected' : '' }}>Menunggu Keputusan Customer</option>
                        <option value="negosiasi" {{ old('status', $offer->status) === 'negosiasi' ? 'selected' : '' }}>Negosiasi</option>
                        <option value="pending" {{ old('status', $offer->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ old('status', $offer->status) === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="no_response" {{ old('status', $offer->status) === 'no_response' ? 'selected' : '' }}>No Response</option>
                        <option value="deal" {{ old('status', $offer->status) === 'deal' ? 'selected' : '' }}>Deal</option>
                    </select>
                    @error('status') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Tanggal Follow Up</label>
                    <input type="date" name="follow_up_date" value="{{ old('follow_up_date', $offer->follow_up_date?->format('Y-m-d')) }}" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Jadwal Meeting</label>
                    <input type="datetime-local" name="meeting_date" value="{{ old('meeting_date', $offer->meeting_date ? $offer->meeting_date->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Alasan / Kendala</label>
                    <textarea name="reason" rows="2" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">{{ old('reason', $offer->reason) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">{{ old('notes', $offer->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-700">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">Update Penawaran</button>
            <a href="{{ route('marketing.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-medium">Batal</a>
        </div>
    </form>
</div>
@endsection
