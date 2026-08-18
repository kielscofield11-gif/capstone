@props(['gradient' => 'from-brand-500 to-brand-700', 'icon' => '', 'label' => '', 'value' => '', 'sublabel' => '', 'href' => null])

@php
$tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif class="bg-gradient-to-br {{ $gradient }} rounded-xl shadow-sm p-4 text-white {{ $href ? 'hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer' : '' }}">
    <div class="flex items-center justify-between mb-2">
        @if($icon)
        <svg class="w-5 h-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
        </svg>
        @endif
        @if($sublabel)
        <span class="text-xs text-white/70 font-medium">{{ $sublabel }}</span>
        @endif
    </div>
    <div class="text-2xl font-bold">{{ $value }}</div>
    <div class="text-xs text-white/80 font-medium uppercase tracking-wide mt-0.5">{{ $label }}</div>
</{{ $tag }}>
