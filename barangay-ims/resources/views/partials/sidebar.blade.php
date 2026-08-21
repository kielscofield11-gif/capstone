<aside id="main-navigation" class="fixed inset-y-0 left-0 z-30 w-[min(18rem,85vw)] md:w-64 bg-brand-900 text-white flex-shrink-0 sidebar-transition md:translate-x-0 md:static md:inset-auto"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       role="navigation" aria-label="Main navigation">
    <div class="flex items-center gap-3 p-5 border-b border-brand-800">
        <img src="/images/logo.png" alt="Barangay Logo" class="w-9 h-9 shrink-0 object-contain bg-white rounded p-1">
        <div class="min-w-0">
            <h1 class="font-bold text-sm truncate">Barangay Concepcion</h1>
            <p class="text-xs text-brand-300 truncate">{{ auth()->user()->name }}</p>
        </div>
    </div>

    <nav class="p-3 space-y-1 overflow-y-auto overscroll-contain" style="max-height: calc(100dvh - 5rem);">
        <x-sidebar-link :route="'dashboard'" :icon="'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'" :label="'Dashboard'"/>

        <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-brand-400">Records</p>
        </div>

        <x-sidebar-link :route="'residents.*'" :icon="'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'" :label="'Residents'"/>
        <x-sidebar-link :route="'households.*'" :icon="'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'" :label="'Households'"/>
        <x-sidebar-link :route="'blotters.*'" :icon="'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'" :label="'Blotter Records'"/>

        <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-brand-400">Services</p>
        </div>

        <x-sidebar-link :route="'documents.*'" :extra="'document-types.*'" :icon="'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'" :label="'Document Requests'"/>
        <x-sidebar-link :route="'document-types.*'" :icon="'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'" :label="'Document Types'"/>

        <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-brand-400">Insights</p>
        </div>

        <x-sidebar-link :route="'reports.*'" :icon="'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'" :label="'Reports'"/>

        @can('viewAny', \App\Models\User::class)
            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-brand-400">Administration</p>
            </div>
            <x-sidebar-link :route="'users.*'" :icon="'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'" :label="'User Management'"/>
            <x-sidebar-link :route="'audit-logs.*'" :icon="'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'" :label="'Audit Logs'"/>
            <x-sidebar-link :route="'document-templates.*'" :icon="'M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z'" :label="'Document Templates'"/>
        @endcan
    </nav>

    <button @click="sidebarOpen = false" class="absolute top-3 right-3 min-w-11 min-h-11 inline-flex items-center justify-center rounded-lg text-brand-300 hover:text-white hover:bg-brand-800 md:hidden" aria-label="Close sidebar">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</aside>

@auth
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/40 md:hidden" x-cloak aria-hidden="true"></div>
@endauth
