@extends('layouts.app')

@section('title', $resident->full_name)
@section('header', $resident->full_name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="text-center mb-4">
                <div class="w-16 h-16 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xl font-bold mx-auto mb-3">
                    {{ substr($resident->full_name, 0, 2) }}
                </div>
                <h3 class="font-semibold text-gray-900">{{ $resident->full_name }}</h3>
                <p class="text-xs text-gray-400 capitalize">{{ $resident->gender }} &middot; {{ $resident->age }} yrs old</p>
            </div>

            <div class="space-y-3">
                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Classification</h4>
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5 text-sm">
                        <span class="w-2 h-2 rounded-full {{ $resident->is_voter ? 'bg-emerald-500' : 'bg-gray-200' }}"></span>
                        <span class="{{ $resident->is_voter ? 'text-gray-900 font-medium' : 'text-gray-400' }}">Voter</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-sm">
                        <span class="w-2 h-2 rounded-full {{ $resident->is_pwd ? 'bg-emerald-500' : 'bg-gray-200' }}"></span>
                        <span class="{{ $resident->is_pwd ? 'text-gray-900 font-medium' : 'text-gray-400' }}">PWD</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-sm">
                        <span class="w-2 h-2 rounded-full {{ $resident->is_senior ? 'bg-emerald-500' : 'bg-gray-200' }}"></span>
                        <span class="{{ $resident->is_senior ? 'text-gray-900 font-medium' : 'text-gray-400' }}">Senior</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-sm">
                        <span class="w-2 h-2 rounded-full {{ $resident->is_4ps ? 'bg-emerald-500' : 'bg-gray-200' }}"></span>
                        <span class="{{ $resident->is_4ps ? 'text-gray-900 font-medium' : 'text-gray-400' }}">4Ps</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-sm">
                        <span class="w-2 h-2 rounded-full {{ $resident->is_household_head ? 'bg-emerald-500' : 'bg-gray-200' }}"></span>
                        <span class="{{ $resident->is_household_head ? 'text-gray-900 font-medium' : 'text-gray-400' }}">Head</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('exports.clearance.pdf', $resident) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm font-medium hover:bg-emerald-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Barangay Clearance
                </a>
                <a href="{{ route('residents.edit', $resident) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand-800 text-white rounded-lg text-sm font-medium hover:bg-brand-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Resident
                </a>
                <button type="button" onclick="openModal_deleteModal('{{ route('residents.destroy', $resident) }}')" class="inline-flex w-full items-center justify-center gap-2 px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Resident
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Contact & Address</h4>
            <div class="space-y-2.5 text-sm">
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ $resident->phone ?? 'No phone' }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>{{ $resident->email ?? 'No email' }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>{{ $resident->purok ?? 'No purok' }}{{ $resident->street_address ? ' - '.$resident->street_address : '' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-3 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Personal Information</h3>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 text-xs">Birth Date</dt>
                        <dd class="font-medium text-gray-900">{{ $resident->birth_date->format('M d, Y') }} ({{ $resident->age }} yrs old)</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Birthplace</dt>
                        <dd class="font-medium text-gray-900">{{ $resident->birthplace ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Gender</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ $resident->gender }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Civil Status</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ $resident->civil_status }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Nationality</dt>
                        <dd class="font-medium text-gray-900">{{ $resident->nationality ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Blood Type</dt>
                        <dd class="font-medium text-gray-900">{{ $resident->blood_type ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Occupation</dt>
                        <dd class="font-medium text-gray-900">{{ $resident->occupation ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-900">Household</h3>
            </div>
            @if($resident->household)
                <a href="{{ route('households.show', $resident->household) }}" class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-700 font-medium text-sm">
                    {{ $resident->household->household_number }} &mdash; {{ $resident->household->purok ?? 'No purok' }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <p class="text-gray-400 text-sm">Not assigned to any household.</p>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Blotter Records</h3>
            </div>
            <div class="p-5">
                <h4 class="text-sm font-medium text-gray-500 mb-3">As Complainant</h4>
                @forelse($resident->complaints as $b)
                    <a href="{{ route('blotters.show', $b) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div>
                            <span class="text-sm font-medium text-gray-900 group-hover:text-brand-700">{{ $b->blotter_number }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ $b->incident_type }}</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $b->status == 'pending' ? 'bg-amber-50 text-amber-700' : ($b->status == 'resolved' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-600') }}">{{ ucfirst($b->status) }}</span>
                    </a>
                @empty
                    <p class="text-gray-400 text-sm">No blotter records as complainant.</p>
                @endforelse

                <h4 class="text-sm font-medium text-gray-500 mt-4 mb-3">As Respondent</h4>
                @forelse($resident->responses as $b)
                    <a href="{{ route('blotters.show', $b) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div>
                            <span class="text-sm font-medium text-gray-900 group-hover:text-brand-700">{{ $b->blotter_number }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ $b->incident_type }}</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $b->status == 'pending' ? 'bg-amber-50 text-amber-700' : ($b->status == 'resolved' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-600') }}">{{ ucfirst($b->status) }}</span>
                    </a>
                @empty
                    <p class="text-gray-400 text-sm">No blotter records as respondent.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Document Requests</h3>
            </div>
            <div class="p-5">
                @forelse($resident->documentRequests as $doc)
                    <a href="{{ route('documents.show', $doc) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div>
                            <span class="text-sm font-medium text-gray-900 group-hover:text-brand-700">{{ $doc->control_number }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ $doc->documentType->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">₱{{ number_format($doc->fee_amount, 2) }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $doc->status == 'pending' ? 'bg-amber-50 text-amber-700' : ($doc->status == 'released' ? 'bg-emerald-50 text-emerald-700' : ($doc->status == 'approved' ? 'bg-blue-50 text-blue-700' : 'bg-gray-50 text-gray-600')) }}">{{ ucfirst($doc->status) }}</span>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-400 text-sm">No document requests.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection