@extends('layouts.app')

@section('title', 'Document ' . $document->control_number)
@section('header', 'Document ' . $document->control_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Document Details</h3>
                <x-status-badge :status="$document->status">{{ ucfirst($document->status) }}</x-status-badge>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 min-[390px]:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm break-anywhere">
                    <div>
                        <dt class="text-gray-500 text-xs">Control Number</dt>
                        <dd class="font-medium text-gray-900">{{ $document->control_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Resident</dt>
                        <dd><a href="{{ route('residents.show', $document->resident) }}" class="font-medium text-brand-600 hover:text-brand-700">{{ $document->resident->full_name }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Document Type</dt>
                        <dd class="font-medium text-gray-900">{{ $document->documentType->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Fee</dt>
                        <dd class="font-medium text-gray-900">₱{{ number_format($document->fee_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Date Requested</dt>
                        <dd class="font-medium text-gray-900">{{ $document->created_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    @if($document->approved_date)
                    <div>
                        <dt class="text-gray-500 text-xs">Date Approved</dt>
                        <dd class="font-medium text-gray-900">{{ $document->approved_date->format('M d, Y') }}</dd>
                    </div>
                    @endif
                    @if($document->released_date)
                    <div>
                        <dt class="text-gray-500 text-xs">Date Released</dt>
                        <dd class="font-medium text-gray-900">{{ $document->released_date->format('M d, Y') }}</dd>
                    </div>
                    @endif
                </dl>

                @if($document->purpose)
                <div class="mt-4">
                    <dt class="text-gray-500 text-xs mb-1">Purpose</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-4">{{ $document->purpose }}</dd>
                </div>
                @endif

                @if($document->remarks)
                <div class="mt-4">
                    <dt class="text-gray-500 text-xs mb-1">Remarks</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-4">{{ $document->remarks }}</dd>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Audit Trail</h4>
            <div class="space-y-2 text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Requested by: <strong>{{ $document->requestedBy?->name ?? 'N/A' }}</strong></span>
                </div>
                @if($document->approvedBy)
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Approved by: <strong>{{ $document->approvedBy->name }}</strong></span>
                </div>
                @endif
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $document->created_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            @can('render', $document)
            <a href="{{ route('documents.preview', $document) }}" target="_blank" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Preview Document</a>
            <a href="{{ route('documents.generated-pdf', $document) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">Download PDF</a>
            @if($document->issued_at)<p class="text-xs text-emerald-700">Issued snapshot preserved {{ $document->issued_at->format('M d, Y h:i A') }}.</p>@endif
            @endcan
            @can('update', $document)
            <a href="{{ route('documents.edit', $document) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand-800 text-white rounded-lg text-sm font-medium hover:bg-brand-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            @endcan
                @if($document->status == 'pending')
                    @can('approve', $document)
                    <form action="{{ route('documents.approve', $document) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Approve Request
                        </button>
                    </form>
                    @endcan
                @endif
                <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                    <p class="font-medium text-gray-900">{{ $document->documentType?->processing_days !== null ? 'Estimated Processing Time: '.$document->documentType->processing_days.' day(s)' : 'Processing time not specified' }}</p>
                    <p class="mt-2 text-xs font-medium text-gray-500">Requirements</p>
                    <p class="mt-1 whitespace-pre-line text-gray-700">{{ $document->documentType?->requirements ?: 'No requirements specified.' }}</p>
                    @if($document->documentType && !$document->documentType->is_active)<p class="mt-2 text-xs font-semibold text-amber-700">This historical request uses an inactive document type.</p>@endif
                </div>
                @if($document->status == 'approved')
                    @can('release', $document)
                    <form action="{{ route('documents.release', $document) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Mark Released
                        </button>
                    </form>
                    @endcan
                @endif
                @if(in_array($document->status, ['pending', 'approved']))
                    @can('cancel', $document)
                    <form action="{{ route('documents.cancel', $document) }}" method="POST"
                          onsubmit="return confirm('Cancel this request?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Cancel Request
                        </button>
                    </form>
                    @endcan
                @endif
            @can('delete', $document)
            <button type="button" onclick="openModal_deleteModal('{{ route('documents.destroy', $document) }}')" class="inline-flex w-full items-center justify-center gap-2 px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete Request
            </button>
            @endcan
        </div>
    </div>
</div>
@endsection
