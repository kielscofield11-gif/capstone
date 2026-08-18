@props(['name' => 'modal', 'title' => 'Confirm Action', 'type' => 'danger', 'method' => 'DELETE', 'confirmText' => 'Delete'])

@php
$typeConfig = [
'danger' => ['icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z', 'iconBg' => 'bg-red-100', 'iconColor' => 'text-red-600', 'buttonBg' => 'bg-red-600 hover:bg-red-700 focus:ring-red-200'],
'warning' => ['icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z', 'iconBg' => 'bg-amber-100', 'iconColor' => 'text-amber-600', 'buttonBg' => 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-200'],
'info' => ['icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'iconBg' => 'bg-blue-100', 'iconColor' => 'text-blue-600', 'buttonBg' => 'bg-brand-800 hover:bg-brand-900 focus:ring-brand-200'],
];
$config = $typeConfig[$type] ?? $typeConfig['danger'];
@endphp

<div x-data="{ open: false, actionUrl: '', init() { window['openModal_{{ $name }}'] = (url) => { this.actionUrl = url; this.open = true; } } }"
     x-cloak>
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="open = false" class="fixed inset-0 bg-black/40 transition-opacity"></div>
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative bg-white rounded-xl shadow-xl border border-gray-100 p-6 w-full max-w-md sm:mx-auto text-left">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full {{ $config['iconBg'] }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 {{ $config['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 id="modal-title" class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                        <div class="mt-1 text-sm text-gray-500">{{ $slot }}</div>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button @click="open = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                    <form :action="actionUrl" method="POST" class="inline">
                        @csrf
                        @method($method)
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-all {{ $config['buttonBg'] }}">
                            {{ $confirmText }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
