@props(['status' => '', 'styleMap' => '[]', 'size' => 'sm'])

@php
$sizeClasses = $size === 'sm' ? 'text-xs px-2.5 py-1' : 'text-sm px-3 py-1.5';
$defaults = [
'pending' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500'],
'hearing' => ['bg-blue-50 text-blue-700 border-blue-200', 'bg-blue-500'],
'approved' => ['bg-blue-50 text-blue-700 border-blue-200', 'bg-blue-500'],
'released' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500'],
'resolved' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500'],
'dismissed' => ['bg-gray-50 text-gray-600 border-gray-200', 'bg-gray-400'],
'cancelled' => ['bg-gray-50 text-gray-600 border-gray-200', 'bg-gray-400'],
'active' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500'],
'inactive' => ['bg-gray-50 text-gray-600 border-gray-200', 'bg-gray-400'],
];
$parsedStyles = is_string($styleMap) ? json_decode($styleMap, true) : $styleMap;
$merged = array_merge($defaults, is_array($parsedStyles) ? $parsedStyles : []);
$selected = $merged[$status] ?? ['bg-gray-50 text-gray-600 border-gray-200', 'bg-gray-400'];
@endphp

<span class="inline-flex items-center gap-1.5 {{ $sizeClasses }} rounded-full font-medium border {{ $selected[0] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $selected[1] }}"></span>
    {{ $slot }}
</span>
