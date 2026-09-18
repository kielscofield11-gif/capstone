@extends('layouts.app')

@section('title', 'Add Resident')
@section('header', 'Add Resident')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-3xl mx-auto">
    <form method="POST" action="{{ route('residents.store') }}" class="p-6 md:p-8 space-y-8">
        @csrf
        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-input-field name="first_name" label="First Name" :required="true" placeholder="Juan" value="{{ old('first_name') }}" class="sm:col-span-1" />
                <x-input-field name="middle_name" label="Middle Name" placeholder="Dela Cruz" value="{{ old('middle_name') }}" />
                <x-input-field name="last_name" label="Last Name" :required="true" placeholder="Santos" value="{{ old('last_name') }}" />
                <x-input-field name="suffix" label="Suffix" placeholder="Jr., III" value="{{ old('suffix') }}" />
            </div>
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Demographics
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-input-field name="birth_date" label="Birth Date" type="date" :required="true" value="{{ old('birth_date') }}" />
                <x-input-field name="birthplace" label="Birthplace" placeholder="City/Municipality" value="{{ old('birthplace') }}" />
                <x-input-field name="gender" label="Gender" type="select" :required="true" value="{{ old('gender') }}" :options="['male' => 'Male', 'female' => 'Female', 'other' => 'Other']" />
                <x-input-field name="civil_status" label="Civil Status" type="select" :required="true" value="{{ old('civil_status') }}" :options="['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated']" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <x-input-field name="nationality" label="Nationality" value="{{ old('nationality', 'Filipino') }}" />
                <x-input-field name="occupation" label="Occupation" placeholder="Occupation" value="{{ old('occupation') }}" />
                <x-input-field name="blood_type" label="Blood Type" placeholder="A+" value="{{ old('blood_type') }}" />
            </div>
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input-field name="phone" label="Phone" placeholder="09XX-XXX-XXXX" value="{{ old('phone') }}" />
                <x-input-field name="email" label="Email" type="email" placeholder="juandelacruz@email.com" value="{{ old('email') }}" />
            </div>
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Address
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input-field name="purok" label="Purok" placeholder="Purok 1" value="{{ old('purok') }}" />
                <x-input-field name="street_address" label="Street Address" placeholder="123 Rizal St." value="{{ old('street_address') }}" />
            </div>
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Household & Classification
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Household</label>
                    <select name="household_id" id="household_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        <option value="">None</option>
                        @foreach($households as $h)
                            <option value="{{ $h->id }}" data-context="{{ $h->street_address ?: $h->purok }}" data-members='@json($h->residents->map(fn($r) => ["name" => $r->full_name, "head" => $r->is_household_head]))' {{ old('household_id', $preselectedHouseholdId ?? '') == $h->id ? 'selected' : '' }}>
                                {{ $h->household_number }} - {{ $h->purok ?? 'No purok' }}
                            </option>
                        @endforeach
                    </select>
                    <div id="household-context" class="mt-2 text-xs text-gray-600"></div>
                    @error('household_id')<p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    @error('is_household_head')<p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Classification</label>
                    <style>
                        .pill-cb:has(input:checked) { background-color: #1e3a5f !important; border-color: #1e3a5f !important; color: #fff !important; }
                    </style>
                    <div class="flex flex-wrap gap-2">
                        <label class="pill-cb inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                            <input type="checkbox" name="is_household_head" value="1" {{ old('is_household_head') ? 'checked' : '' }} class="sr-only"> Head
                        </label>
                        <label class="pill-cb inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                            <input type="checkbox" name="is_voter" value="1" {{ old('is_voter') ? 'checked' : '' }} class="sr-only"> Voter
                        </label>
                        <label class="pill-cb inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                            <input type="checkbox" name="is_pwd" value="1" {{ old('is_pwd') ? 'checked' : '' }} class="sr-only"> PWD
                        </label>
                        <label class="pill-cb inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                            <input type="checkbox" name="is_senior" value="1" {{ old('is_senior') ? 'checked' : '' }} class="sr-only"> Senior
                        </label>
                        <label class="pill-cb inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                            <input type="checkbox" name="is_4ps" value="1" {{ old('is_4ps') ? 'checked' : '' }} class="sr-only"> 4Ps
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-4 border-t border-gray-100 [&>*]:w-full sm:[&>*]:w-auto [&>*]:min-h-11">
            <a href="{{ route('residents.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">
                Save Resident
            </button>
        </div>
    </form>
</div>
@endsection
@push('scripts')<script>const householdSelect=document.getElementById('household_id');function showHousehold(){const option=householdSelect?.options[householdSelect.selectedIndex],box=document.getElementById('household-context');if(!option?.value){box.textContent='No household selected.';return;}let members=[];try{members=JSON.parse(option.dataset.members||'[]')}catch(e){}box.textContent=`${option.dataset.context||'Address not specified'} · Members: ${members.length ? members.map(m=>m.name+(m.head?' (Head)':'')).join(', ') : 'None'}`;}householdSelect?.addEventListener('change',showHousehold);showHousehold();</script>@endpush
