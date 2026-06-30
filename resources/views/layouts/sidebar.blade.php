<nav class="flex-1 overflow-y-auto p-4">
    
    {{-- ✅ HELPER: Fungsi untuk ubah label role di tampilan --}}
    @php
        $getRoleLabel = function($roleName) {
            return match($roleName) {
                'super_admin' => 'Admin',
                'pegawai' => 'Pegawai',
                'marketing' => 'Marketing',
                'direktur' => 'Direktur',
                'customer' => 'Customer',
                default => ucfirst(str_replace('_', ' ', $roleName)),
            };
        };
    @endphp

    <!-- DASHBOARD LINK (Berbeda per role) -->
    @if(Auth::check())
        @php
            $userRole = Auth::user()?->role?->name ?? '';
            $projectRouteParam = request()->route('project');
            $activeProjectCategory = request('category') ?: (is_object($projectRouteParam) ? ($projectRouteParam->category ?? null) : null);
            $projectSectionActive = request()->routeIs('admin.projects.*')
                || request()->routeIs('admin.tasks.*')
                || request()->routeIs('projects.detail');
        @endphp

        {{-- ✅ Direktur: Dashboard Khusus Direktur --}}
        @if($userRole === 'direktur')
        <a href="{{ route('direktur.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('direktur.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard Direktur
        </a>

        {{-- ✅ Admin: Dashboard Utama --}}
        @elseif($userRole === 'super_admin')
        <a href="{{ route('main.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('main.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard Utama
        </a>

        {{-- ✅ Pegawai: Employee Dashboard --}}
        @elseif(false && $userRole === 'pegawai')
        <a href="{{ route('employee.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('employee.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard Pegawai
        </a>

        {{-- ✅ Customer: Portal Customer --}}
        @elseif($userRole === 'customer')
        <a href="{{ route('customer.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('customer.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Portal Customer
        </a>

        {{-- ✅ Marketing: Dashboard Marketing --}}
        @elseif($userRole === 'marketing')
        <a href="{{ route('marketing.index') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('marketing.*') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard Marketing
        </a>
        @endif
    @endif

    <!-- MENU BIDANG - HANYA UNTUK DIREKTUR & ADMIN -->
    @if(Auth::check() && in_array(Auth::user()?->role?->name ?? '', ['direktur', 'super_admin']))
    <div class="mt-6">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 px-3">BIDANG</h3>
        
        {{-- Web & Aplikasi --}}
        <a href="{{ route('projects.category.detail', ['category' => 'web']) }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ in_array($activeProjectCategory, ['web'], true) && (request()->routeIs('projects.category.detail') || request()->routeIs('projects.detail')) ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Web & Aplikasi
        </a>
        
        {{-- Internet & Jaringan --}}
        <a href="{{ route('projects.category.detail', ['category' => 'internet']) }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ in_array($activeProjectCategory, ['internet'], true) && (request()->routeIs('projects.category.detail') || request()->routeIs('projects.detail')) ? 'bg-green-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
            </svg>
            Internet & Jaringan
        </a>
        
        {{-- CCTV --}}
        <a href="{{ route('projects.category.detail', ['category' => 'cctv']) }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ in_array($activeProjectCategory, ['cctv'], true) && (request()->routeIs('projects.category.detail') || request()->routeIs('projects.detail')) ? 'bg-purple-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            CCTV
        </a>
    </div>
    @endif

    <!-- MENU MARKETING (DIREKTUR & ADMIN) -->
    @if(Auth::check() && in_array(Auth::user()?->role?->name ?? '', ['direktur', 'super_admin']))
    <div class="mt-6">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 px-3">MARKETING</h3>
        
        <a href="{{ route('admin.marketing.index') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.marketing.*') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Laporan Marketing
        </a>
    </div>
    @endif

    <!-- MENU MARKETING (PEGAWAI - Input Sendiri) -->
    @if(false && Auth::check() && (Auth::user()?->role?->name ?? '') === 'pegawai')
    <div class="mt-6">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 px-3">MARKETING</h3>
        
        <a href="{{ route('marketing.index') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('marketing.*') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Penawaran Saya
        </a>
    </div>
    @endif

    <!-- MENU TUGAS SAYA - HANYA PEGAWAI -->
    @if(Auth::check() && (Auth::user()?->role?->name ?? '') === 'pegawai')
    <div class="mt-6">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 px-3">TUGAS</h3>
        
        <a href="{{ route('employee.tasks.index') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('employee.tasks.*') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Tugas Saya
        </a>
    </div>
    @endif

    <!-- MENU ADMIN - HANYA ADMIN (SUPER_ADMIN) -->
    @if(Auth::check() && (Auth::user()?->role?->name ?? '') === 'super_admin')
    <div class="mt-6">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 px-3">ADMIN</h3>
        
        <a href="{{ route('admin.projects.index') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $projectSectionActive ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Kelola Proyek
        </a>
        
        <a href="{{ route('admin.customers.index') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.customers.*') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Kelola Customer
        </a>

        <a href="{{ route('admin.users.index') }}" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            Kelola Akun Pegawai
        </a>
    </div>
    @endif

    <!-- MENU CUSTOMER - HANYA CUSTOMER -->
    @if(Auth::check() && (Auth::user()?->role?->name ?? '') === 'customer')
    <div class="mt-6">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 px-3">PROYEK SAYA</h3>
        
        @if(isset($customerCategories) && is_array($customerCategories))
            @foreach($customerCategories as $cat)
            <a href="{{ route('customer.category', ['category' => $cat]) }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('customer.category') && request('category') === $cat ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                {{ ucfirst($cat) }}
            </a>
            @endforeach
        @endif
    </div>
    @endif

</nav>
