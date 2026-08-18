@extends('layouts.app')

@section('title', 'Add Document Type')
@section('header', 'Add Document Type')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('document-types.store') }}" class="p-6 md:p-8 space-y-5">
        @csrf
        <x-input-field name="name" label="Name" :required="true" placeholder="Barangay Clearance" value="{{ old('name') }}" />
        <x-input-field name="description" label="Description" type="textarea" placeholder="Description of this document type..." value="{{ old('description') }}" />
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee Amount <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                <input type="number" name="fee_amount" value="{{ old('fee_amount', '0') }}" step="0.01" min="0" required
                    class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('fee_amount') border-red-400 ring-2 ring-red-200 @enderror">
            </div>
            @error('fee_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('document-types.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Save</button>
        </div>
    </form>
</div>
@endsection
