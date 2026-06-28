@extends('layouts.app')

@section('title', 'Manage Customers')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-1">👥 Manage Customers</h1>
            <p class="text-gray-400">Kelola customer dan pantau status proyek</p>
        </div>
        <a href="{{ route('admin.customers.create') }}" 
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Customer
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-4 mb-4 rounded">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-4 mb-4 rounded">
        {{ session('error') }}
    </div>
    @endif

    <!-- Search & Filter -->
    <div class="bg-gray-800 rounded-lg p-4 border border-gray-700 mb-6">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex gap-4 flex-wrap">
            <!-- Search Input -->
            <div class="flex-1 min-w-[300px]">
                <input type="text" name="search" 
                       placeholder="Cari customer atau company..." 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Category Filter -->
            <div class="w-48">
                <select name="category" 
                        onchange="this.form.submit()"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    <option value="web" {{ request('category') == 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                    <option value="internet" {{ request('category') == 'internet' ? 'selected' : '' }}>Layanan Internet</option>
                    <option value="cctv" {{ request('category') == 'cctv' ? 'selected' : '' }}>CCTV</option>
                </select>
            </div>

            <!-- Sort Dropdown -->
            <div class="w-40">
                <select name="sort" onchange="this.form.submit()" 
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>A-Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Z-A</option>
                    <option value="projects_desc" {{ request('sort') == 'projects_desc' ? 'selected' : '' }}>Most Projects</option>
                    <option value="projects_asc" {{ request('sort') == 'projects_asc' ? 'selected' : '' }}>Least Projects</option>
                    <option value="created_desc" {{ request('sort') == 'created_desc' ? 'selected' : '' }}>Newest</option>
                </select>
            </div>

            <!-- Search Button -->
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                Cari
            </button>

            <!-- Reset Button -->
            @if(request('search') || request('category'))
            <a href="{{ route('admin.customers.index') }}" 
               class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700 border-b border-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Kategori Bidang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Total Proyek</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status Proyek</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($customers as $customer)
                    @php
                        $ongoing = $customer->customerProjects()->where('status', 'ongoing')->count();
                        $done = $customer->customerProjects()->where('status', 'done')->count();
                        $offer = 0;
                        $total = $customer->customer_projects_count;
                        $categories = $customer->categories ?? [];
                    @endphp
                    <tr class="hover:bg-gray-700/50 transition">
                        <!-- Customer Info -->
                        <td class="px-6 py-4">
                            <div class="font-semibold text-white">{{ $customer->company }}</div>
                            <div class="text-sm text-gray-400">{{ $customer->name }}</div>
                            @if($customer->phone)
                            <div class="text-xs text-gray-500 mt-1">📞 {{ $customer->phone }}</div>
                            @endif
                        </td>

                        <!-- Email -->
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-300">{{ $customer->email }}</div>
                        </td>

                        <!-- Kategori Bidang -->
                        <td class="px-6 py-4">
                            @if(count($categories) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($categories as $cat)
                                <span class="px-2 py-1 text-xs rounded-full font-semibold
                                    @if($cat === 'web') bg-blue-500/20 text-blue-400
                                    @elseif($cat === 'internet') bg-green-500/20 text-green-400
                                    @else bg-purple-500/20 text-purple-400 @endif">
                                    @if($cat === 'web') Web
                                    @elseif($cat === 'internet') Internet
                                    @else CCTV @endif
                                </span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-gray-500 text-sm">Belum ada proyek</span>
                            @endif
                        </td>

                        <!-- Total Proyek -->
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-white">{{ $total }}</span>
                            <span class="text-xs text-gray-400">proyek</span>
                        </td>

                        <!-- Status Proyek -->
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($ongoing > 0)
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-500/20 text-blue-400">
                                    🔵 {{ $ongoing }} Ongoing
                                </span>
                                @endif
                                @if($done > 0)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400">
                                    ✅ {{ $done }} Done
                                </span>
                                @endif
                                @if($offer > 0)
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-400">
                                    ⏳ {{ $offer }} Offer
                                </span>
                                @endif
                                @if($total == 0)
                                <span class="text-gray-500 text-xs">Belum ada proyek</span>
                                @endif
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.projects.index', ['customer' => $customer->id]) }}" 
                                   class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded transition" 
                                   title="Lihat Proyek">
                                    📦 Proyek
                                </a>
                                <a href="{{ route('admin.customers.edit', $customer) }}" 
                                   class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-xs rounded transition">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus customer {{ $customer->company }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p class="text-lg mb-2">Belum ada customer</p>
                            <p class="text-sm">Mulai dengan menambahkan customer pertama Anda</p>
                            <a href="{{ route('admin.customers.create') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                + Tambah Customer
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    <!-- ✅ Summary Stats (Menggunakan variabel dari controller - dihitung dari projects table) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm mb-1">Total Customer</p>
            <p class="text-2xl font-bold text-white">{{ $totalCustomers ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm mb-1">Web & Aplikasi</p>
            <p class="text-2xl font-bold text-blue-400">{{ $webCustomers ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm mb-1">Layanan Internet</p>
            <p class="text-2xl font-bold text-green-400">{{ $internetCustomers ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm mb-1">CCTV</p>
            <p class="text-2xl font-bold text-purple-400">{{ $cctvCustomers ?? 0 }}</p>
        </div>
    </div>
</div>
@endsection
