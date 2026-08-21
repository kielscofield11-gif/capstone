@extends('layouts.app')

@section('title', 'Edit Document Type')
@section('header', 'Edit Document Type')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('document-types.update', $documentType) }}" class="p-6 md:p-8 space-y-5">
        @csrf @method('PUT')
        <x-input-field name="name" label="Name" :required="true" value="{{ old('name', $documentType->name) }}" />
        <x-input-field name="description" label="Description" type="textarea" value="{{ old('description', $documentType->description) }}" />
        <x-input-field name="requirements" label="Requirements (one per line)" type="textarea" value="{{ old('requirements', $documentType->requirements) }}" />
        <x-input-field name="processing_days" label="Estimated Processing Days" type="number" value="{{ old('processing_days', $documentType->processing_days) }}" />
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee Amount <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                <input type="number" name="fee_amount" value="{{ old('fee_amount', $documentType->fee_amount) }}" step="0.01" min="0" required
                    class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('fee_amount') border-red-400 ring-2 ring-red-200 @enderror">
            </div>
            @error('fee_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center gap-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $documentType->is_active) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
            </label>
            <span class="text-sm font-medium text-gray-700">Active</span>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-4 border-t border-gray-100 [&>*]:w-full sm:[&>*]:w-auto [&>*]:min-h-11">
            <a href="{{ route('document-types.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Update</button>
        </div>
    </form>
</div>
@endsection
