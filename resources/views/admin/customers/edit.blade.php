@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="p-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">✏️ Edit Customer</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Update data customer</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Perusahaan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="company" value="{{ old('company', $customer->company) }}" required
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('company')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Contact Person <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" required
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        No. Telepon
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Kategori Proyek (Berdasarkan Proyek yang Sudah Dibuat)
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($assignedCategories as $cat)
                        <span class="px-3 py-1 text-sm rounded-full 
                            @if($cat === 'web') bg-blue-100 text-blue-700
                            @elseif($cat === 'internet') bg-green-100 text-green-700
                            @else bg-purple-100 text-purple-700 @endif">
                            @if($cat === 'web') Web & Aplikasi
                            @elseif($cat === 'internet') Layanan Internet
                            @else CCTV @endif
                        </span>
                        @endforeach
                        @if(empty($assignedCategories))
                        <span class="text-gray-400 text-sm">Belum ada proyek untuk customer ini</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Kategori akan otomatis terupdate saat Anda membuat proyek untuk customer ini
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.customers.index') }}" 
                       class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection