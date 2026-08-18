<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Barangay Concepcion Information Management System">
    <meta name="theme-color" content="#1e3a5f">
    <title>@yield('title', 'Dashboard') — Barangay Concepcion</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e3a5f',
                            900: '#172e46',
                            950: '#0f1f30',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.2s ease-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideIn: { '0%': { transform: 'translateX(-100%)' }, '100%': { transform: 'translateX(0)' } },
                        slideUp: { '0%': { transform: 'translateY(10px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } },
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
        @media print { .no-print { display: none !important; } body { background: white !important; } }
        .toast-enter { animation: slideUp 0.3s ease-out; }
        .toast-exit { animation: fadeIn 0.2s ease-out reverse; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:border focus:border-gray-300 focus:rounded-lg focus:shadow-lg focus:text-brand-800">
        Skip to main content
    </a>

    @auth
        <div x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="darkMode ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')" class="min-h-screen flex">
            @include('partials.sidebar')

            <div class="flex-1 flex flex-col min-w-0">
                @include('partials.header')

                <main id="main-content" class="flex-1 p-4 md:p-6 lg:p-8">
                    @if(session('success'))
                        <x-toast type="success" :message="session('success')" />
                    @endif
                    @if(session('error'))
                        <x-toast type="error" :message="session('error')" />
                    @endif
                    @if(session('warning'))
                        <x-toast type="warning" :message="session('warning')" />
                    @endif
                    @if(session('info'))
                        <x-toast type="info" :message="session('info')" />
                    @endif

                    @yield('content')
                </main>

                <footer class="bg-white border-t px-6 py-3 text-sm text-gray-400 no-print">
                    &copy; {{ date('Y') }} Barangay Concepcion Information Management System. All rights reserved.
                </footer>
            </div>
        </div>
    @endauth

    @auth
        <x-modal name="deleteModal" title="Delete Record" type="danger" :confirm-text="'Delete'" :method="'DELETE'">
            Are you sure you want to delete this record? This action cannot be undone.
        </x-modal>
    @endauth

    @guest
        @yield('content')
    @endguest

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const btn = form.querySelector('[type="submit"]');
            if (btn && !btn.dataset.noLoading && !btn.dataset.modalTrigger) {
                btn.disabled = true;
                const origText = btn.innerHTML;
                btn.dataset.origText = origText;
                const loadingText = btn.dataset.loadingText || 'Saving...';
                btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> ' + loadingText;
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[x-data]').forEach(el => {
                    if (el.__x) el.__x.$data.show = false;
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>