@props(['icon' => '', 'title' => 'No data found', 'message' => 'Try adjusting your search or filters.', 'action' => null, 'actionLabel' => null, 'colspan' => null])

@php
$svgPath = $icon ?: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z';
@endphp

@if($colspan)
<tr>
    <td colspan="{{ $colspan }}" class="px-5 py-12 text-center">
@endif
<div class="flex flex-col items-center justify-center py-12 text-center">
    <svg class="w-14 h-14 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="{{ $svgPath }}"/>
    </svg>
    <p class="text-sm font-semibold text-gray-500">{{ $title }}</p>
    @if($message)<p class="text-xs text-gray-400 mt-1 max-w-xs">{{ $message }}</p>@endif
    @if($action && $actionLabel)
        <a href="{{ $action }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ $actionLabel }}
        </a>
    @endif
</div>
@if($colspan)
    </td>
</tr>
@endif
