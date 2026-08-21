@extends('layouts.app')

@section('title', 'Household ' . $household->household_number)
@section('header', 'Household ' . $household->household_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Household Details</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500 text-xs">Number</dt>
                    <dd class="font-medium text-gray-900">{{ $household->household_number }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs">Purok</dt>
                    <dd class="font-medium text-gray-900">{{ $household->purok ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs">Address</dt>
                    <dd class="font-medium text-gray-900">{{ $household->street_address ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs">Members</dt>
                    <dd><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-50 text-brand-700 text-sm font-bold">{{ $household->residents->count() }}</span></dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs">Status</dt>
                    <dd>
                        <x-status-badge :status="$household->is_active ? 'active' : 'inactive'">{{ $household->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                    </dd>
                </div>
            </dl>
            @can('update', $household)
            <a href="{{ route('households.edit', $household) }}" class="mt-4 inline-flex items-center justify-center gap-2 w-full px-4 py-2 bg-brand-800 text-white rounded-lg text-sm font-medium hover:bg-brand-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Household
            </a>
            @endcan
        </div>
    </div>

    <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-gray-900">Members ({{ $household->residents->count() }})</h3>
                @can('create', \App\Models\Resident::class)
                <a href="{{ route('residents.create', ['household_id' => $household->id]) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Member
                </a>
                @endcan
            </div>
            @if($household->residents->where('is_household_head', true)->count() > 1)
            <div class="mx-5 mt-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">Multiple residents are marked as household heads. No head was changed automatically.</div>
            @endif
            <div class="responsive-table" tabindex="0" role="region" aria-label="Household members table">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Gender</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Age</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($household->residents as $resident)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3">
                                    <a href="{{ route('residents.show', $resident) }}" class="font-medium text-gray-900 hover:text-brand-700">{{ $resident->full_name }}</a>
                                </td>
                                <td class="px-5 py-3 capitalize text-gray-600 hidden sm:table-cell">{{ $resident->gender }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $resident->age }}</td>
                                <td class="px-5 py-3 hidden sm:table-cell">
                                    @if($resident->is_household_head)
                                        <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full font-medium bg-blue-50 text-blue-700 border border-blue-200">Head</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center"><div class="text-gray-400"><svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg><p class="text-sm font-medium">No members in this household.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
