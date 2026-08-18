@props(['route', 'icon' => '', 'label' => '', 'extra' => ''])

@php
    $isActive = request()->routeIs($route);
    if ($extra) {
        $isActive = $isActive || request()->routeIs($extra);
    }
@endphp

<a href="{{ route(str_replace('.*', '.index', $route)) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ $isActive ? 'bg-brand-700/60 text-white shadow-sm' : 'text-brand-200 hover:bg-brand-800/50 hover:text-white' }}"
   @if($isActive) aria-current="page" @endif>
    <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-brand-300' : 'text-brand-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
    </svg>
    <span>{{ $label }}</span>
    @if($isActive)
        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-300"></span>
    @endif
</a>