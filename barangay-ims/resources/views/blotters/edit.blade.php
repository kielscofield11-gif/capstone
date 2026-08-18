@extends('layouts.app')

@section('title', 'Edit Blotter')
@section('header', 'Edit Blotter')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-2xl mx-auto">
    <form method="POST" action="{{ route('blotters.update', $blotter) }}" class="p-6 md:p-8 space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input-field name="blotter_number" label="Blotter Number" :required="true" placeholder="B-2024-001" value="{{ old('blotter_number', $blotter->blotter_number) }}" />
            <x-input-field name="incident_type" label="Incident Type" :required="true" placeholder="Physical Injury" value="{{ old('incident_type', $blotter->incident_type) }}" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input-field name="complainant_id" label="Complainant" type="select" :required="true" value="{{ old('complainant_id', $blotter->complainant_id) }}" :options="['' => 'Select Resident'] + $residents->pluck('full_name', 'id')->toArray()" />
            <x-input-field name="respondent_id" label="Respondent" type="select" :required="true" value="{{ old('respondent_id', $blotter->respondent_id) }}" :options="['' => 'Select Resident'] + $residents->pluck('full_name', 'id')->toArray()" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input-field name="incident_date" label="Incident Date" type="date" :required="true" value="{{ old('incident_date', $blotter->incident_date->format('Y-m-d')) }}" />
            <x-input-field name="incident_location" label="Incident Location" placeholder="Barangay Hall" value="{{ old('incident_location', $blotter->incident_location) }}" />
        </div>

        <x-input-field name="details" label="Details" type="textarea" :required="true" placeholder="Describe the incident..." value="{{ old('details', $blotter->details) }}" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input-field name="status" label="Status" type="select" value="{{ old('status', $blotter->status) }}" :options="['pending' => 'Pending', 'hearing' => 'Hearing', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed']" />
            <x-input-field name="hearing_date" label="Hearing Date" type="date" value="{{ old('hearing_date', $blotter->hearing_date?->format('Y-m-d')) }}" />
        </div>

        <x-input-field name="resolution" label="Resolution" type="textarea" placeholder="Resolution details..." value="{{ old('resolution', $blotter->resolution) }}" />

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('blotters.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Update Blotter</button>
        </div>
    </form>
</div>
@endsection
