@props(['route' => '', 'searchPlaceholder' => 'Search...', 'searchName' => 'search', 'filters' => []])
@php
$hasActiveFilters = collect($filters)->contains(function($f) { return request($f['name'] ?? ''); }) || request('search');
@endphp

<form method="GET" id="filter-form" class="grid w-full grid-cols-1 sm:flex sm:flex-wrap gap-2 items-end">
    <div class="w-full sm:w-auto">
        <input type="text" name="{{ $searchName }}" placeholder="{{ $searchPlaceholder }}" value="{{ request($searchName) }}"
            aria-label="Search" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm w-full sm:w-44 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors">
    </div>

    @foreach($filters as $filter)
        @php $fName = $filter['name'] ?? ''; @endphp
        @if(($filter['type'] ?? 'select') === 'select')
        <div class="w-full sm:w-auto">
            <select name="{{ $fName }}" aria-label="{{ $filter['placeholder'] ?? $fName }}" class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors">
                <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                @foreach(($filter['options'] ?? []) as $optValue => $optLabel)
                    <option value="{{ $optValue }}" {{ request($fName) == $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                @endforeach
            </select>
        </div>
        @elseif(($filter['type'] ?? '') === 'date')
        <div class="w-full sm:w-auto">
            <input type="date" name="{{ $fName }}" value="{{ request($fName) }}"
                aria-label="{{ $filter['placeholder'] ?? $fName }}" class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors">
        </div>
        @endif
    @endforeach

    <button type="submit" class="min-h-11 justify-center bg-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        Filter
    </button>
    <a href="{{ $route }}" class="min-h-11 justify-center text-sm text-gray-500 hover:text-gray-700 px-2 py-2 inline-flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        Clear
    </a>
</form>

@if($hasActiveFilters)
<div class="flex flex-wrap items-center gap-1.5 mt-3">
    <span class="text-xs text-gray-400 font-medium">Active filters:</span>
    @if(request('search'))
        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
            "{{ request('search') }}"
            @php $withoutSearch = collect(request()->query())->except(['search', 'page'])->toArray(); @endphp
            <a href="{{ url()->current() . (count($withoutSearch) ? '?' . http_build_query($withoutSearch) : '') }}" class="hover:text-brand-900">&times;</a>
        </span>
    @endif
    @foreach($filters as $filter)
        @php $fName = $filter['name'] ?? ''; @endphp
        @if(request($fName))
            @php $withoutFilter = collect(request()->query())->except([$fName, 'page'])->toArray(); @endphp
            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-gray-50 text-gray-600 border border-gray-200">
                {{ $filter['placeholder'] ?? $fName }}: {{ request($fName) }}
                <a href="{{ url()->current() . (count($withoutFilter) ? '?' . http_build_query($withoutFilter) : '') }}" class="hover:text-gray-800">&times;</a>
            </span>
        @endif
    @endforeach
</div>
@endif
