@extends('layouts.app')

@section('title', 'Edit Akun Pegawai - Ninama')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-blue-400 hover:underline text-sm">← Kembali ke Daftar Pegawai</a>
        <h1 class="text-2xl font-bold text-white mt-2">Edit Akun Pegawai</h1>
        <p class="text-gray-400 text-sm">Perbarui informasi akun pegawai.</p>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-gray-800 p-6 rounded-lg border border-gray-700 space-y-4">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">No. Telepon (WhatsApp)</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0812..." class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Role / Tipe Akun <span class="text-red-500">*</span></label>
            <select name="role_id" id="roleSelect" required class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
            @error('role_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- ✅ Field Bidang (Hanya untuk Pegawai) --}}
        <div id="bidangField" style="display: none;">
            <label class="block text-sm font-medium text-gray-400 mb-1">Bidang <span class="text-red-500">*</span></label>
            <select name="bidang" id="bidangSelect" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Pilih Bidang --</option>
                <option value="web" {{ old('bidang', $user->bidang) == 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                <option value="internet" {{ old('bidang', $user->bidang) == 'internet' ? 'selected' : '' }}>Internet & Jaringan</option>
                <option value="cctv" {{ old('bidang', $user->bidang) == 'cctv' ? 'selected' : '' }}>CCTV</option>
            </select>
            @error('bidang') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- ✅ Field Divisi/Jabatan (Dinamis berdasarkan Bidang) --}}
        <div id="divisiField" style="display: none;">
            <label class="block text-sm font-medium text-gray-400 mb-1">Divisi / Jabatan <span class="text-red-500">*</span></label>
            <select name="jabatan" id="divisiSelect" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Pilih Divisi --</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Divisi akan muncul sesuai bidang yang dipilih</p>
            @error('jabatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">Simpan Perubahan</button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-medium">Batal</a>
        </div>
    </form>
</div>

<script>
// ✅ Data Divisi per Bidang
const divisiData = {
    web: [
        'UI/UX',
        'Frontend',
        'Backend',
        'Testing',
        'DevOps',
        'Project Management'
    ],
    internet: [
        'Network Engineer',
        'NOC',
        'Technical Support',
        'Server Administrator',
        'Fiber Optic Technician',
        'Maintenance',
        'Project Management'
    ],
    cctv: [
        'CCTV Installer',
        'Configuration',
        'Monitoring',
        'Maintenance',
        'Troubleshooting',
        'Project Management'
    ]
};

// ✅ Toggle field berdasarkan role
document.getElementById('roleSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const roleName = selectedOption.text.toLowerCase();
    const bidangField = document.getElementById('bidangField');
    const divisiField = document.getElementById('divisiField');
    const bidangSelect = document.getElementById('bidangSelect');
    const divisiSelect = document.getElementById('divisiSelect');
    
    if (roleName === 'pegawai') {
        bidangField.style.display = 'block';
        bidangSelect.required = true;
        
        // Trigger bidang change untuk load divisi
        bidangSelect.dispatchEvent(new Event('change'));
    } else {
        bidangField.style.display = 'none';
        divisiField.style.display = 'none';
        bidangSelect.required = false;
        divisiSelect.required = false;
        bidangSelect.value = '';
        divisiSelect.value = '';
    }
});

// ✅ Toggle divisi berdasarkan bidang
document.getElementById('bidangSelect').addEventListener('change', function() {
    const selectedBidang = this.value;
    const divisiField = document.getElementById('divisiField');
    const divisiSelect = document.getElementById('divisiSelect');
    
    // Reset divisi
    divisiSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
    
    if (selectedBidang && divisiData[selectedBidang]) {
        divisiField.style.display = 'block';
        divisiSelect.required = true;
        
        // Populate divisi options
        divisiData[selectedBidang].forEach(divisi => {
            const option = document.createElement('option');
            option.value = divisi;
            option.textContent = divisi;
            divisiSelect.appendChild(option);
        });
        
        // Pre-fill divisi jika ada nilai lama
        const currentDivisi = '{{ old("jabatan", $user->jabatan) }}';
        if (currentDivisi && divisiData[selectedBidang].includes(currentDivisi)) {
            divisiSelect.value = currentDivisi;
        }
    } else {
        divisiField.style.display = 'none';
        divisiSelect.required = false;
    }
});

// Trigger on load untuk set initial state
document.getElementById('roleSelect').dispatchEvent(new Event('change'));
</script>
@endsection