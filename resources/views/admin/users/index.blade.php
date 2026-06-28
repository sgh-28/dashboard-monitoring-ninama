@extends('layouts.app')

@section('title', 'Kelola Akun Pegawai - Ninama')

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Kelola Akun Pegawai</h1>
            <p class="text-gray-400 text-sm">Manajemen akun internal perusahaan (Pegawai & Marketing)</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Pegawai
        </a>
    </div>

    {{-- TABEL USER --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-700/50 text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 font-medium">Nama</th>
                        <th class="px-6 py-3 font-medium">Email</th>
                        <th class="px-6 py-3 font-medium">No. Telepon</th>
                        <th class="px-6 py-3 font-medium">Bidang</th>
                        <th class="px-6 py-3 font-medium">Divisi</th>
                        <th class="px-6 py-3 font-medium">Role</th>
                        <th class="px-6 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-700/30 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ $user->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $user->phone ?? '-' }}</td>
                        
                        {{-- ✅ KOLOM BIDANG --}}
                        <td class="px-6 py-4">
                            @if($user->role->name === 'pegawai' && $user->bidang)
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($user->bidang === 'web') bg-blue-900/50 text-blue-300 border border-blue-500/30
                                    @elseif($user->bidang === 'internet') bg-green-900/50 text-green-300 border border-green-500/30
                                    @elseif($user->bidang === 'cctv') bg-purple-900/50 text-purple-300 border border-purple-500/30
                                    @else bg-gray-700 text-gray-300 @endif">
                                    {{ $user->bidang_name }}
                                </span>
                            @else
                                <span class="text-gray-500 text-xs">-</span>
                            @endif
                        </td>
                        
                        {{-- ✅ KOLOM DIVISI --}}
                        <td class="px-6 py-4 text-gray-300">
                            @if($user->jabatan)
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-700 text-gray-300 border border-gray-600">
                                    {{ $user->jabatan }}
                                </span>
                            @else
                                <span class="text-gray-500 text-xs">-</span>
                            @endif
                        </td>
                        
                        {{-- ✅ KOLOM ROLE: Menggunakan label yang user-friendly --}}
                        <td class="px-6 py-4">
                            @php
                                $roleName = $user->role->name;
                                // ✅ Ubah label super_admin menjadi "Admin" di tampilan
                                $roleLabel = match($roleName) {
                                    'super_admin' => 'Admin',
                                    'pegawai' => 'Pegawai',
                                    'marketing' => 'Marketing',
                                    'direktur' => 'Direktur',
                                    'customer' => 'Customer',
                                    default => ucfirst(str_replace('_', ' ', $roleName)),
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($roleName === 'super_admin') bg-red-900/50 text-red-300 border border-red-500/30
                                @elseif($roleName === 'pegawai') bg-green-900/50 text-green-300 border border-green-500/30
                                @elseif($roleName === 'marketing') bg-yellow-900/50 text-yellow-300 border border-yellow-500/30
                                @else bg-gray-700 text-gray-300 @endif">
                                {{ $roleLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="px-3 py-1 text-xs bg-yellow-600 hover:bg-yellow-700 text-white rounded transition">Edit</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded transition">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">Belum ada data pegawai.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection