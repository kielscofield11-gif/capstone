@extends('layouts.app')

@section('title', 'Blotter ' . $blotter->blotter_number)
@section('header', 'Blotter ' . $blotter->blotter_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Incident Details</h3>
                <x-status-badge :status="$blotter->status" size="base">{{ ucfirst($blotter->status) }}</x-status-badge>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 text-xs">Blotter Number</dt>
                        <dd class="font-medium text-gray-900">{{ $blotter->blotter_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Incident Type</dt>
                        <dd class="font-medium text-gray-900">{{ $blotter->incident_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Incident Date</dt>
                        <dd class="font-medium text-gray-900">{{ $blotter->incident_date->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Location</dt>
                        <dd class="font-medium text-gray-900">{{ $blotter->incident_location ?? 'N/A' }}</dd>
                    </div>
                    @if($blotter->hearing_date)
                    <div>
                        <dt class="text-gray-500 text-xs">Hearing Date</dt>
                        <dd class="font-medium text-gray-900">{{ $blotter->hearing_date->format('F d, Y') }}</dd>
                    </div>
                    @endif
                </dl>

                <div class="mt-4">
                    <dt class="text-gray-500 text-xs mb-1">Details</dt>
                    <dd class="text-sm text-gray-900 whitespace-pre-wrap bg-gray-50 rounded-lg p-4">{{ $blotter->details }}</dd>
                </div>

                @if($blotter->resolution)
                <div class="mt-4">
                    <dt class="text-gray-500 text-xs mb-1">Resolution</dt>
                    <dd class="text-sm text-gray-900 whitespace-pre-wrap bg-emerald-50 rounded-lg p-4">{{ $blotter->resolution }}</dd>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Complainant</h4>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold shrink-0">
                    {{ substr($blotter->complainant->full_name ?? 'NA', 0, 2) }}
                </div>
                <div>
                    <a href="{{ route('residents.show', $blotter->complainant) }}" class="text-sm font-medium text-gray-900 hover:text-brand-700">
                        {{ $blotter->complainant->full_name ?? 'N/A' }}
                    </a>
                    <p class="text-xs text-gray-400">{{ $blotter->complainant->purok ?? 'No purok' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Respondent</h4>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-sm font-bold shrink-0">
                    {{ substr($blotter->respondent->full_name ?? 'NA', 0, 2) }}
                </div>
                <div>
                    <a href="{{ route('residents.show', $blotter->respondent) }}" class="text-sm font-medium text-gray-900 hover:text-brand-700">
                        {{ $blotter->respondent->full_name ?? 'N/A' }}
                    </a>
                    <p class="text-xs text-gray-400">{{ $blotter->respondent->purok ?? 'No purok' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Audit Trail</h4>
            <div class="space-y-2 text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Created by: <strong>{{ $blotter->createdBy?->name ?? 'N/A' }}</strong></span>
                </div>
                @if($blotter->resolvedBy)
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Resolved by: <strong>{{ $blotter->resolvedBy->name }}</strong></span>
                </div>
                @endif
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $blotter->created_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <a href="{{ route('blotters.edit', $blotter) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand-800 text-white rounded-lg text-sm font-medium hover:bg-brand-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Blotter
            </a>
            <button type="button" onclick="openModal_deleteModal('{{ route('blotters.destroy', $blotter) }}')" class="inline-flex w-full items-center justify-center gap-2 px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete Blotter
            </button>
        </div>
    </div>
</div>
@endsection