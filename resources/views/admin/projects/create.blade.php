@extends('layouts.app')

@section('title', 'Tambah Proyek Baru')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">➕ Tambah Proyek Baru</h1>
        <p class="text-gray-400">Isi formulir di bawah untuk membuat proyek baru</p>
    </div>

    <div id="paste-status" class="hidden mb-4 rounded-lg border px-4 py-3 text-sm"></div>

    <form action="{{ route('admin.projects.store') }}" method="POST" class="bg-gray-800 rounded-lg p-6 border border-gray-700 space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- LEFT COLUMN: Customer Information --}}
            <div class="space-y-6">
                <div class="border-b border-gray-700 pb-4">
                    <h3 class="text-lg font-semibold text-white mb-4">👤 Customer Information</h3>
                    
                    {{-- Pilih Customer yang Sudah Ada --}}
                    <div class="mb-4">
                        <label class="block text-sm text-gray-400 mb-2">Pilih Customer yang Sudah Ada</label>
                        <select name="customer_id" id="existing-customer" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->company ?? $customer->name }} ({{ $customer->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center text-gray-500 text-sm mb-4">atau</div>

                    {{-- Buat Customer Baru --}}
                    <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-4">
                        <div class="flex items-start gap-3 mb-3">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <h4 class="text-blue-400 font-medium">Buat Customer Baru + Akun Login</h4>
                                <p class="text-xs text-gray-400 mt-1">Customer akan mendapat akun untuk login dan melihat progress proyek</p>
                            </div>
                        </div>

                        <button type="button"
                                id="paste-marketing-data"
                                class="mb-4 inline-flex items-center gap-2 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg transition">
                            Paste Data Marketing
                        </button>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Nama Perusahaan *</label>
                                <input type="text" name="new_customer_company" value="{{ old('new_customer_company') }}" 
                                       class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-sm text-white"
                                       placeholder="Contoh: PT. Digital Kreatif">
                                @error('new_customer_company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Nama Contact Person *</label>
                                <input type="text" name="new_customer_name" value="{{ old('new_customer_name') }}" 
                                       class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-sm text-white"
                                       placeholder="Nama PIC"
                                       pattern="[A-Za-zÀ-ÿ\s.'-]+"
                                       title="Nama PIC hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.">
                                @error('new_customer_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Email (untuk login) *</label>
                                <input type="email" name="new_customer_email" value="{{ old('new_customer_email') }}" 
                                       class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-sm text-white"
                                       placeholder="email@perusahaan.com">
                                @error('new_customer_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs text-gray-400 mb-1">No. Telepon</label>
                                <input type="text" name="new_customer_phone" value="{{ old('new_customer_phone') }}" 
                                       class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-sm text-white"
                                       placeholder="08123456789">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Password *</label>
                                    <input type="password" name="new_customer_password" 
                                           class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-sm text-white"
                                           placeholder="Password">
                                    @error('new_customer_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Konfirmasi Password *</label>
                                    <input type="password" name="new_customer_password_confirmation" 
                                           class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-sm text-white"
                                           placeholder="Ulangi password">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Project Information --}}
            <div class="space-y-6">
                <div class="border-b border-gray-700 pb-4">
                    <h3 class="text-lg font-semibold text-white mb-4">📦 Informasi Proyek</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Nama Proyek *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required 
                                   class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white"
                                   placeholder="Contoh: Website E-Commerce">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Kategori Bidang *</label>
                            <select name="category" id="category-select" required 
                                    class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                                <option value="">Pilih Kategori</option>
                                <option value="web" {{ old('category') == 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                                <option value="internet" {{ old('category') == 'internet' ? 'selected' : '' }}>Internet & Jaringan</option>
                                <option value="cctv" {{ old('category') == 'cctv' ? 'selected' : '' }}>CCTV</option>
                            </select>
                            @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Status Proyek</label>
                            <select name="status" id="project-status" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                                <option value="ongoing" {{ old('status', 'ongoing') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="done" {{ old('status') == 'done' ? 'selected' : '' }}>Selesai (Done)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}" 
                                       class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Deadline *</label>
                                <input type="date" name="deadline" value="{{ old('deadline') }}" required 
                                       class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                                @error('deadline') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Target SLA (%)</label>
                            <input type="number" name="sla" value="{{ old('sla', 100) }}" min="0" max="100" 
                                   class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Alamat / Lokasi</label>
                            <textarea name="address" rows="2" 
                                      class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white"
                                      placeholder="Alamat lokasi proyek (opsional)">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DIVISI DINAMIS (Full Width) --}}
        <div class="bg-gray-700/50 p-4 rounded-lg border border-gray-600">
            <h3 class="text-lg font-semibold text-white mb-2">🔧 Bagian / Divisi yang Dikerjakan</h3>
            <p class="text-sm text-gray-400 mb-3">Pilih kategori bidang di atas untuk memunculkan divisi yang relevan</p>
            
            <div id="divisions-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <p class="text-gray-500 text-sm col-span-full">Menunggu pemilihan kategori...</p>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-3 pt-4 border-t border-gray-700">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                💾 Simpan Proyek
            </button>
            <a href="{{ route('admin.projects.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category-select');
    const divisionsContainer = document.getElementById('divisions-container');
    const existingCustomerSelect = document.getElementById('existing-customer');
    const pasteButton = document.getElementById('paste-marketing-data');
    const pasteStatus = document.getElementById('paste-status');
    const picNameInput = document.querySelector('[name="new_customer_name"]');
    
    if (!categorySelect || !divisionsContainer) return;

    function setField(name, value) {
        const field = document.querySelector(`[name="${name}"]`);
        if (!field || value === undefined || value === null) return;

        field.value = name === 'new_customer_name' ? sanitizePersonName(value) : value;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function sanitizePersonName(value) {
        return (value || '').replace(/[0-9]/g, '');
    }

    function showPasteStatus(message, type = 'success') {
        if (!pasteStatus) return;

        pasteStatus.textContent = message;
        pasteStatus.className = type === 'success'
            ? 'mb-4 rounded-lg border border-emerald-500/30 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-200'
            : 'mb-4 rounded-lg border border-red-500/30 bg-red-900/30 px-4 py-3 text-sm text-red-200';
    }

    function parseMarketingData(text) {
        const data = {};

        text.split(/\r?\n/).forEach(line => {
            const separatorIndex = line.indexOf(':');
            if (separatorIndex === -1) return;

            const key = line.slice(0, separatorIndex).trim().toLowerCase();
            const value = line.slice(separatorIndex + 1).trim();
            if (!value || value === '-') return;

            data[key] = value;
        });

        return data;
    }

    function normalizeCategory(value) {
        const normalized = (value || '').toLowerCase();

        if (normalized.includes('internet')) return 'internet';
        if (normalized.includes('cctv')) return 'cctv';
        if (normalized.includes('web')) return 'web';

        return '';
    }

    function clearDivisionChecks() {
        document.querySelectorAll('[name="divisions[]"]').forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    function applyMarketingData(data) {
        if (existingCustomerSelect) {
            existingCustomerSelect.value = '';
            existingCustomerSelect.dispatchEvent(new Event('change'));
        }

        setField('new_customer_company', data['nama perusahaan'] || '');
        setField('new_customer_name', data['nama customer/kontak'] || '');
        setField('new_customer_email', data['email'] || '');
        setField('new_customer_phone', data['nomor hp'] || '');
        setField('new_customer_password', '');
        setField('new_customer_password_confirmation', '');

        setField('name', data['nama project'] || data['nama perusahaan'] || '');
        setField('category', normalizeCategory(data['bidang']));
        setField('status', 'ongoing');
        setField('address', data['alamat'] || '');

        clearDivisionChecks();
        setTimeout(clearDivisionChecks, 400);
    }

    // Handle category change - load divisions dynamically
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        divisionsContainer.innerHTML = '<p class="text-gray-400 text-sm col-span-full">Memuat divisi...</p>';
        
        if (!category) {
            divisionsContainer.innerHTML = '<p class="text-gray-500 text-sm col-span-full">Menunggu pemilihan kategori...</p>';
            return;
        }

        // Fetch divisions via AJAX
        fetch(`/admin/projects/divisions/${category}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(divisions => {
                divisionsContainer.innerHTML = '';
                if (!divisions || divisions.length === 0) {
                    divisionsContainer.innerHTML = '<p class="text-gray-500 text-sm col-span-full">Tidak ada divisi untuk kategori ini.</p>';
                    return;
                }
                
                const oldDivisions = @json(old('divisions', []));
                
                divisions.forEach((div, index) => {
                    const checked = oldDivisions.includes(div) ? 'checked' : '';
                    
                    const divHtml = `
                        <label class="flex items-center p-3 bg-gray-800 rounded-lg border border-gray-600 hover:bg-gray-700 cursor-pointer transition">
                            <input type="checkbox" name="divisions[]" value="${div}" ${checked} 
                                   class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 bg-gray-700 border-gray-600">
                            <span class="ml-3 text-sm text-gray-300">${div}</span>
                        </label>
                    `;
                    divisionsContainer.insertAdjacentHTML('beforeend', divHtml);
                });
            })
            .catch(error => {
                console.error('Error loading divisions:', error);
                divisionsContainer.innerHTML = '<p class="text-red-400 text-sm col-span-full">⚠️ Gagal memuat divisi.</p>';
            });
    });

    // Handle customer selection - hide/show new customer fields
    if (existingCustomerSelect) {
        existingCustomerSelect.addEventListener('change', function() {
            const newCustomerFields = document.querySelectorAll('[name^="new_customer"]');
            if (this.value) {
                // If existing customer selected, disable new customer inputs
                newCustomerFields.forEach(field => {
                    field.disabled = true;
                    field.classList.add('opacity-50');
                });
            } else {
                // If no existing customer, enable new customer inputs
                newCustomerFields.forEach(field => {
                    field.disabled = false;
                    field.classList.remove('opacity-50');
                });
            }
        });

        // Trigger on load
        existingCustomerSelect.dispatchEvent(new Event('change'));
    }

    if (pasteButton) {
        pasteButton.addEventListener('click', async function () {
            try {
                const text = await navigator.clipboard.readText();
                const data = parseMarketingData(text);

                if (!data['nama perusahaan'] && !data['nama project']) {
                    showPasteStatus('Clipboard belum berisi data marketing yang valid.', 'error');
                    return;
                }

                applyMarketingData(data);
                showPasteStatus('Data marketing berhasil ditempel. Password, deadline, dan pilihan divisi tetap perlu diisi admin.');
            } catch (error) {
                showPasteStatus('Browser tidak mengizinkan akses clipboard. Klik Copy Data lagi, lalu coba Paste Data Marketing.', 'error');
            }
        });
    }

    if (picNameInput) {
        picNameInput.addEventListener('input', function () {
            this.value = sanitizePersonName(this.value);
        });
    }

    // Trigger load if category already selected (edit mode)
    if (categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
