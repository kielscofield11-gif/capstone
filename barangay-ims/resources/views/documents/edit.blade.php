@extends('layouts.app')

@section('title', 'Edit Document Request')
@section('header', 'Edit Document Request')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('documents.update', $document) }}" class="p-6 md:p-8 space-y-5">
        @csrf @method('PUT')

        <x-input-field name="control_number" label="Control Number" :required="true" placeholder="DC-2024-001" value="{{ old('control_number', $document->control_number) }}" />

        <x-input-field name="resident_id" label="Resident" type="select" :required="true" value="{{ old('resident_id', $document->resident_id) }}" :options="['' => 'Select Resident'] + $residents->pluck('full_name', 'id')->toArray()" />

        <x-input-field name="document_type_id" label="Document Type" type="select" :required="true" value="{{ old('document_type_id', $document->document_type_id) }}" :options="$documentTypes->pluck('name', 'id')->toArray()" />

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee Amount</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                <input type="number" name="fee_amount" value="{{ old('fee_amount', $document->fee_amount) }}" step="0.01" min="0"
                    class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
        </div>

        <x-input-field name="purpose" label="Purpose" type="textarea" value="{{ old('purpose', $document->purpose) }}" />

        <x-input-field name="remarks" label="Remarks" type="textarea" value="{{ old('remarks', $document->remarks) }}" />

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('documents.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Update Request</button>
        </div>
    </form>
</div>
@endsection
