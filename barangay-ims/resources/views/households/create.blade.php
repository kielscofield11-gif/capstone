@extends('layouts.app')

@section('title', 'Add Household')
@section('header', 'Add Household')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('households.store') }}" class="p-6 md:p-8 space-y-5">
        @csrf
        <x-input-field name="household_number" label="Household Number" :required="true" placeholder="H-2024-001" value="{{ old('household_number') }}" />
        <x-input-field name="purok" label="Purok" placeholder="Purok 1" value="{{ old('purok') }}" />
        <x-input-field name="street_address" label="Street Address" placeholder="123 Rizal St." value="{{ old('street_address') }}" />
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('households.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Save Household</button>
        </div>
    </form>
</div>
@endsection
