@extends('layouts.app')

@section('title', 'Edit Household')
@section('header', 'Edit Household')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('households.update', $household) }}" class="p-6 md:p-8 space-y-5">
        @csrf @method('PUT')
        <x-input-field name="household_number" label="Household Number" :required="true" value="{{ old('household_number', $household->household_number) }}" />
        <x-input-field name="purok" label="Purok" value="{{ old('purok', $household->purok) }}" />
        <x-input-field name="street_address" label="Street Address" value="{{ old('street_address', $household->street_address) }}" />
        <div class="flex items-center gap-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $household->is_active) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
            </label>
            <span class="text-sm font-medium text-gray-700">Active</span>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('households.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Update Household</button>
        </div>
    </form>
</div>
@endsection
