@extends('layouts.app')

@section('title', 'Document Requests')
@section('header', 'Document Requests')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-4 md:p-5 border-b border-gray-100 flex flex-wrap gap-3 justify-between items-center">
        <x-filter-bar :route="route('documents.index')" search-placeholder="Search control # or name..." :filters="[
            ['name' => 'status', 'placeholder' => 'All Status', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'released' => 'Released', 'cancelled' => 'Cancelled']],
            ['name' => 'document_type_id', 'placeholder' => 'All Types', 'options' => $documentTypes->pluck('name', 'id')->toArray()],
            ['name' => 'date_from', 'type' => 'date'],
            ['name' => 'date_to', 'type' => 'date'],
        ]" />
        <a href="{{ route('documents.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition-all shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Request
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Control #</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Resident</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Document Type</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Fee</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Date</th>
                    <th class="text-right px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($documents as $doc)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 md:px-5 py-3">
                            <a href="{{ route('documents.show', $doc) }}" class="font-medium text-gray-900 hover:text-brand-700">
                                {{ $doc->control_number }}
                            </a>
                        </td>
                        <td class="px-4 md:px-5 py-3 text-gray-600">{{ $doc->resident->full_name }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden md:table-cell">{{ $doc->documentType->name ?? 'N/A' }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden sm:table-cell">₱{{ number_format($doc->fee_amount, 2) }}</td>
                        <td class="px-4 md:px-5 py-3">
                            <x-status-badge :status="$doc->status">{{ ucfirst($doc->status) }}</x-status-badge>
                        </td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $doc->created_at->format('M d, Y') }}</td>
                        <td class="px-4 md:px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('documents.show', $doc) }}" class="p-2 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" aria-label="View document {{ $doc->control_number }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('documents.edit', $doc) }}" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" aria-label="Edit document {{ $doc->control_number }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @can('manage-documents')
                                    @if($doc->status == 'pending')
                                        <form action="{{ route('documents.approve', $doc) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors" aria-label="Approve document {{ $doc->control_number }}" title="Approve">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($doc->status == 'approved')
                                        <form action="{{ route('documents.release', $doc) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" aria-label="Release document {{ $doc->control_number }}" title="Release">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($doc->status, ['pending', 'approved']))
                                        <form action="{{ route('documents.cancel', $doc) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" onclick="return confirm('Cancel this request?')" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="Cancel document {{ $doc->control_number }}" title="Cancel">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                <button type="button" onclick="openModal_deleteModal('{{ route('documents.destroy', $doc) }}')" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="Delete document {{ $doc->control_number }}" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" title="No document requests found" message="Try adjusting your search or filters." :colspan="7" />
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">{{ $documents->links() }}</div>
</div>
@endsection