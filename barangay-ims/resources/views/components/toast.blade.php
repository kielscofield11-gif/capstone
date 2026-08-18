@props(['type' => 'success', 'message' => ''])

@php
$styles = [
'success' => ['bg-emerald-50 border-emerald-200 text-emerald-800', 'text-emerald-500', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
'error' => ['bg-red-50 border-red-200 text-red-800', 'text-red-500', 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
'warning' => ['bg-amber-50 border-amber-200 text-amber-800', 'text-amber-500', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z'],
'info' => ['bg-blue-50 border-blue-200 text-blue-800', 'text-blue-500', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
];
$selected = $styles[$type] ?? $styles['success'];
@endphp

<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     role="alert"
     class="mb-4 {{ $selected[0] }} px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
    <svg class="w-5 h-5 {{ $selected[1] }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $selected[2] }}"/>
    </svg>
    <span class="text-sm font-medium">{{ $message }}</span>
    <button @click="show = false" class="ml-auto {{ $selected[1] }} hover:opacity-70" aria-label="Dismiss">&times;</button>
</div>
