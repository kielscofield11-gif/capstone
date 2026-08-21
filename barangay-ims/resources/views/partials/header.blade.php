<header class="bg-white border-b border-gray-200 px-4 md:px-6 py-3 flex items-center justify-between sticky top-0 z-10 no-print">
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-controls="main-navigation" class="min-w-11 min-h-11 -ml-2 inline-flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 md:hidden" aria-label="Toggle sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
            <h2 class="text-base sm:text-lg font-semibold text-gray-900 break-anywhere">@yield('header', 'Dashboard')</h2>
        </div>
    </div>

    <div class="flex items-center gap-2">
    <div x-data="{ noticesOpen: false }" class="relative">
        <button @click="noticesOpen = !noticesOpen" :aria-expanded="noticesOpen" class="relative min-w-11 min-h-11 inline-flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Notifications">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            @if(auth()->user()->unreadNotifications()->count())<span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center">{{ min(auth()->user()->unreadNotifications()->count(), 99) }}</span>@endif
        </button>
        <div x-show="noticesOpen" x-cloak @click.outside="noticesOpen=false" class="absolute right-0 mt-2 w-[min(22rem,calc(100vw-2rem))] bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
            <div class="px-4 py-3 border-b flex justify-between"><span class="font-semibold text-sm">Notifications</span><a href="{{ route('notifications.index') }}" class="text-xs text-brand-700">View all</a></div>
            @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf @method('PATCH')<button class="min-h-11 w-full text-left px-4 py-3 border-b border-gray-50 hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-blue-50/50' }}"><span class="block text-sm font-medium text-gray-800 break-anywhere">{{ $notification->data['title'] ?? 'Reminder' }}</span><span class="block text-xs text-gray-500 break-anywhere">{{ $notification->data['message'] ?? '' }}</span></button></form>
            @empty <p class="px-4 py-6 text-center text-sm text-gray-500">No notifications.</p> @endforelse
        </div>
    </div>
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors" aria-haspopup="true" :aria-expanded="open">
            <div class="w-8 h-8 rounded-full bg-brand-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="hidden sm:block text-left">
                <p class="text-sm font-medium text-gray-700 leading-tight">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400">{{ auth()->user()->role_label }}</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false"
             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
             x-cloak role="menu">
            <div class="px-4 py-2 border-b border-gray-100 sm:hidden">
                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400">{{ auth()->user()->role_label }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
    </div>
</header>
