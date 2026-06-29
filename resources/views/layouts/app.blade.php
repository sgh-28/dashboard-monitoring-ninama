<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Business Dashboard - Ninama')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <style>
        .app-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        #app-sidebar {
            position: relative;
            z-index: 40;
            width: 16rem;
            flex: 0 0 16rem;
            transform: none;
        }

        #sidebar-overlay,
        #sidebar-close,
        .mobile-topbar {
            display: none;
        }

        .app-main {
            min-width: 0;
            flex: 1 1 auto;
            overflow-y: auto;
        }

        @media (max-width: 1023px) {
            #app-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(16rem, 86vw);
                flex-basis: auto;
                transform: translateX(-100%);
                transition: transform 200ms ease;
            }

            #app-sidebar.sidebar-open {
                transform: translateX(0);
            }

            #sidebar-overlay.sidebar-open {
                display: block;
            }

            #sidebar-close,
            .mobile-topbar {
                display: flex;
            }

            .app-main {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    
    <div class="app-shell">
        <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50"></div>

        <!-- Sidebar -->
        <aside id="app-sidebar" class="flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
            <!-- Logo -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-ninama.png') }}" 
                         alt="Logo Ninama" 
                         class="h-10 w-auto object-contain">
                    <div>
                        <h1 class="text-lg font-bold text-gray-800 dark:text-gray-200">Business Dashboard</h1>
                        <span class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded">Ninama</span>
                    </div>
                    <button type="button" id="sidebar-close" class="ml-auto rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Tutup menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- SIDEBAR DENGAN ROLE-BASED CONDITIONS -->
            @include('layouts.sidebar')

            <!-- USER INFO & LOGOUT -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                            {{ (Auth::user()?->role?->name ?? '') === 'super_admin' ? 'Admin' : (Auth::user()?->name ?? 'User') }}
                        </p>
                        {{-- ✅ PERBAIKAN: Mengubah super_admin menjadi Admin di tampilan --}}
                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                            @php
                                $roleLabel = Auth::user()?->role?->name ?? 'user';
                                if ($roleLabel === 'super_admin') $roleLabel = 'admin';
                            @endphp
                            {{ ucfirst(str_replace('_', ' ', $roleLabel)) }}
                        </p>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>

            <!-- Light/Dark Mode Toggle -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <button id="theme-toggle" 
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <span id="theme-toggle-text">Light Mode</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="app-main bg-gray-50 dark:bg-gray-900">
            <div class="mobile-topbar sticky top-0 z-20 items-center gap-3 border-b border-gray-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
                <button type="button" id="sidebar-open" class="rounded-lg p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">@yield('title', 'Business Dashboard - Ninama')</p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()?->name ?? 'User' }}</p>
                </div>
            </div>
            @if(session('success'))
            <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-4 m-4 rounded">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-4 m-4 rounded">
                {{ session('error') }}
            </div>
            @endif
            
            @yield('content')
        </main>
    </div>

    <script>
        (function() {
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
            const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
            const themeToggleText = document.getElementById('theme-toggle-text');

            function updateThemeIcons() {
                if (document.documentElement.classList.contains('dark')) {
                    themeToggleDarkIcon.classList.add('hidden');
                    themeToggleLightIcon.classList.remove('hidden');
                    themeToggleText.textContent = 'Light Mode';
                } else {
                    themeToggleDarkIcon.classList.remove('hidden');
                    themeToggleLightIcon.classList.add('hidden');
                    themeToggleText.textContent = 'Dark Mode';
                }
            }

            updateThemeIcons();

            themeToggleBtn.addEventListener('click', function() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
                updateThemeIcons();
            });

            const sidebar = document.getElementById('app-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openBtn = document.getElementById('sidebar-open');
            const closeBtn = document.getElementById('sidebar-close');

            function openSidebar() {
                sidebar.classList.add('sidebar-open');
                overlay.classList.add('sidebar-open');
            }

            function closeSidebar() {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('sidebar-open');
            }

            openBtn?.addEventListener('click', openSidebar);
            closeBtn?.addEventListener('click', closeSidebar);
            overlay?.addEventListener('click', closeSidebar);

            sidebar?.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
