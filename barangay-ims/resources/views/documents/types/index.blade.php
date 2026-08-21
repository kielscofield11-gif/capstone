@extends('layouts.app')

@section('title', 'Document Types')
@section('header', 'Document Types')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-4 md:p-5 border-b border-gray-100 flex flex-wrap gap-3 justify-between items-center">
        <p class="text-sm text-gray-500">Manage document types and fees</p>
        @can('create', \App\Models\DocumentType::class)
        <a href="{{ route('document-types.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Type
        </a>
        @endcan
    </div>

    <div class="responsive-table" tabindex="0" role="region" aria-label="Document types table">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Description</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fee</th>
                    <th class="text-center px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requests</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($documentTypes as $dt)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 md:px-5 py-3 font-medium text-gray-900">{{ $dt->name }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden md:table-cell">{{ \Illuminate\Support\Str::limit($dt->description ?? '-', 50) }}</td>
                        <td class="px-4 md:px-5 py-3 font-medium text-gray-900">₱{{ number_format($dt->fee_amount, 2) }}</td>
                        <td class="px-4 md:px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-50 text-brand-700 text-xs font-bold">{{ $dt->document_requests_count }}</span>
                        </td>
                        <td class="px-4 md:px-5 py-3">
                            <x-status-badge :status="$dt->is_active ? 'active' : 'inactive'">{{ $dt->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                        </td>
                        <td class="px-4 md:px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('update', $dt)
                                <a href="{{ route('document-types.edit', $dt) }}" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" aria-label="Edit document type {{ $dt->name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                                @can('delete', $dt)
                                <button type="button" onclick="openModal_deleteModal('{{ route('document-types.destroy', $dt) }}')" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="Delete document type {{ $dt->name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" title="No document types found" message="Create a document type to get started." :colspan="6" />
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">{{ $documentTypes->links() }}</div>
</div>
@endsection
