@extends('layouts.app')

@section('title', 'Residents')
@section('header', 'Residents')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-4 md:p-5 border-b border-gray-100 flex flex-wrap gap-3 justify-between items-center">
        <div class="flex flex-wrap items-end gap-2">
            <x-filter-bar :route="route('residents.index')" search-placeholder="Search name or phone..." :filters="[
                ['name' => 'purok', 'placeholder' => 'All Puroks', 'options' => $puroks->count() ? array_combine($puroks->toArray(), $puroks->toArray()) : []],
                ['name' => 'gender', 'placeholder' => 'All Genders', 'options' => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other']],
                ['name' => 'civil_status', 'placeholder' => 'All Status', 'options' => ['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated']],
            ]" />
            <script>
                document.addEventListener('change', function(e) {
                    if (e.target.matches('.filter-pill input[type="checkbox"]')) {
                        e.target.closest('.filter-pill').classList.toggle('active', e.target.checked);
                    }
                });
                document.querySelectorAll('.filter-pill input[type="checkbox"]:checked').forEach(function(cb) {
                    cb.closest('.filter-pill').classList.add('active');
                });
            </script>
            <style>
                .filter-pill.active { background-color: #1e3a5f !important; border-color: #1e3a5f !important; color: #fff !important; }
            </style>
            <div class="flex items-center gap-1.5 mt-2 md:mt-0">
                <label class="filter-pill text-xs flex items-center gap-1.5 px-2.5 py-1.5 rounded-md border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                    <input type="checkbox" name="senior" form="filter-form" value="1" {{ request('senior') ? 'checked' : '' }} class="sr-only">Senior
                </label>
                <label class="filter-pill text-xs flex items-center gap-1.5 px-2.5 py-1.5 rounded-md border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                    <input type="checkbox" name="pwd" form="filter-form" value="1" {{ request('pwd') ? 'checked' : '' }} class="sr-only">PWD
                </label>
                <label class="filter-pill text-xs flex items-center gap-1.5 px-2.5 py-1.5 rounded-md border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                    <input type="checkbox" name="voter" form="filter-form" value="1" {{ request('voter') ? 'checked' : '' }} class="sr-only">Voter
                </label>
            </div>
        </div>
        @can('create', \App\Models\Resident::class)
        <a href="{{ route('residents.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition-all shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Resident
        </a>
        @endcan
    </div>

    <div class="responsive-table" tabindex="0" role="region" aria-label="Residents table">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Gender</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Age</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Purok</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell">Household</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Contact</th>
                    <th class="text-right px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($residents as $resident)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 md:px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">{{ substr($resident->full_name, 0, 2) }}</div>
                                <div class="min-w-0">
                                    <a href="{{ route('residents.show', $resident) }}" class="font-medium text-gray-900 hover:text-brand-700 truncate block">
                                        {{ $resident->full_name }}
                                    </a>
                                    <div class="flex gap-1 mt-0.5">
                                        @if($resident->is_senior)<span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Senior</span>@endif
                                        @if($resident->is_pwd)<span class="text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-medium">PWD</span>@endif
                                        @if($resident->is_voter)<span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-medium">Voter</span>@endif
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500 sm:hidden break-anywhere">{{ $resident->household?->household_number ?? 'No household' }} · {{ $resident->phone ?? 'No phone' }}</div>
                            </div>
                        </td>
                        <td class="px-4 md:px-5 py-3 capitalize text-gray-600 hidden sm:table-cell">{{ $resident->gender }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden md:table-cell">{{ $resident->age }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden lg:table-cell">{{ $resident->purok ?? '-' }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden xl:table-cell">{{ $resident->household?->household_number ?? '-' }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $resident->phone ?? '-' }}</td>
                        <td class="px-4 md:px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('residents.show', $resident) }}" class="p-2 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" aria-label="View {{ $resident->full_name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @can('update', $resident)
                                <a href="{{ route('residents.edit', $resident) }}" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" aria-label="Edit {{ $resident->full_name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                                @can('delete', $resident)
                                <button type="button" onclick="openModal_deleteModal('{{ route('residents.destroy', $resident) }}')" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="Delete {{ $resident->full_name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" title="No residents found" message="Try adjusting your search or filters." :colspan="7" />
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $residents->links() }}
    </div>
</div>
@endsection
