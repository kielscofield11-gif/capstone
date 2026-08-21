@extends('layouts.app')

@section('title', 'Edit Resident')
@section('header', 'Edit Resident')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-3xl mx-auto">
    <form method="POST" action="{{ route('residents.update', $resident) }}" enctype="multipart/form-data" class="p-6 md:p-8 space-y-8">
        @csrf @method('PUT')
        @if(session('duplicate_candidates'))
        <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900"><p class="font-semibold">Possible duplicate resident found</p>@foreach(session('duplicate_candidates') as $candidate)<p class="mt-1"><a class="underline" href="{{ route('residents.show', $candidate) }}">{{ $candidate->full_name }}</a> — {{ $candidate->birth_date->format('M d, Y') }}</p>@endforeach<label class="mt-3 flex items-center gap-2"><input type="checkbox" name="duplicate_override" value="1"> I confirm this is a different person.</label></div>
        @endif

        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-input-field name="first_name" label="First Name" :required="true" placeholder="Juan" value="{{ old('first_name', $resident->first_name) }}" class="sm:col-span-1" />
                <x-input-field name="middle_name" label="Middle Name" placeholder="Dela Cruz" value="{{ old('middle_name', $resident->middle_name) }}" />
                <x-input-field name="last_name" label="Last Name" :required="true" placeholder="Santos" value="{{ old('last_name', $resident->last_name) }}" />
                <x-input-field name="suffix" label="Suffix" placeholder="Jr., III" value="{{ old('suffix', $resident->suffix) }}" />
            </div>
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Demographics
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-input-field name="birth_date" label="Birth Date" type="date" :required="true" value="{{ old('birth_date', $resident->birth_date->format('Y-m-d')) }}" />
                <x-input-field name="birthplace" label="Birthplace" placeholder="City/Municipality" value="{{ old('birthplace', $resident->birthplace) }}" />
                <x-input-field name="gender" label="Gender" type="select" :required="true" value="{{ old('gender', $resident->gender) }}" :options="['male' => 'Male', 'female' => 'Female', 'other' => 'Other']" />
                <x-input-field name="civil_status" label="Civil Status" type="select" :required="true" value="{{ old('civil_status', $resident->civil_status) }}" :options="['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated']" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <x-input-field name="nationality" label="Nationality" value="{{ old('nationality', $resident->nationality) }}" />
                <x-input-field name="occupation" label="Occupation" placeholder="Occupation" value="{{ old('occupation', $resident->occupation) }}" />
                <x-input-field name="blood_type" label="Blood Type" placeholder="A+" value="{{ old('blood_type', $resident->blood_type) }}" />
            </div>
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input-field name="phone" label="Phone" placeholder="09XX-XXX-XXXX" value="{{ old('phone', $resident->phone) }}" />
                <x-input-field name="email" label="Email" type="email" placeholder="juandelacruz@email.com" value="{{ old('email', $resident->email) }}" />
            </div>
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Address
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input-field name="purok" label="Purok" placeholder="Purok 1" value="{{ old('purok', $resident->purok) }}" />
                <x-input-field name="street_address" label="Street Address" placeholder="123 Rizal St." value="{{ old('street_address', $resident->street_address) }}" />
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
                            <option value="{{ $h->id }}" data-context="{{ $h->street_address ?: $h->purok }}" data-members='@json($h->residents->map(fn($r) => ["name" => $r->full_name, "head" => $r->is_household_head]))' {{ old('household_id', $resident->household_id) == $h->id ? 'selected' : '' }}>
                                {{ $h->household_number }} - {{ $h->purok ?? 'No purok' }}{{ !$h->is_active ? ' (Inactive — current assignment)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div id="household-context" class="mt-2 text-xs text-gray-600"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Classification</label>
                    <style>
                        .pill-cb:has(input:checked) { background-color: #1e3a5f !important; border-color: #1e3a5f !important; color: #fff !important; }
                    </style>
                    <div class="flex flex-wrap gap-2">
                        @php $checks = ['is_household_head' => 'Head', 'is_voter' => 'Voter', 'is_pwd' => 'PWD', 'is_senior' => 'Senior', 'is_4ps' => '4Ps']; @endphp
                        @foreach($checks as $field => $label)
                            <label class="pill-cb inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg border cursor-pointer transition-colors border-gray-200 text-gray-500 hover:border-gray-300">
                                <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, $resident->$field) ? 'checked' : '' }} class="sr-only"> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div x-data="{ preview: null }"><label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Resident Photo</label><div class="flex flex-col sm:flex-row gap-4">@if($resident->photo_path)<img src="{{ Storage::disk('public')->url($resident->photo_path) }}" alt="Current resident photo" class="h-32 w-32 max-w-full rounded-xl border object-cover">@endif<div class="min-w-0 flex-1"><input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" @change="preview && URL.revokeObjectURL(preview); preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null" class="block min-h-11 w-full max-w-full text-sm text-gray-600 file:mr-3 file:min-h-11 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:text-brand-800"><img x-show="preview" :src="preview" alt="Replacement resident photo preview" class="mt-3 h-32 w-32 rounded-xl border object-cover" x-cloak><p class="mt-1 text-xs text-gray-500">Choose from camera or gallery. JPEG, PNG, or WebP; maximum 2 MB.</p>@if($resident->photo_path)<label class="mt-3 flex min-h-11 items-center gap-3 text-sm font-medium text-red-700"><input type="checkbox" name="remove_photo" value="1" class="w-5 h-5"> Remove current photo</label>@endif</div></div>@error('photo')<p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-4 border-t border-gray-100 [&>*]:w-full sm:[&>*]:w-auto [&>*]:min-h-11">
            <a href="{{ route('residents.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Update Resident</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')<script>const householdSelect=document.getElementById('household_id');function showHousehold(){const option=householdSelect?.options[householdSelect.selectedIndex],box=document.getElementById('household-context');if(!option?.value){box.textContent='No household selected.';return;}let members=[];try{members=JSON.parse(option.dataset.members||'[]')}catch(e){}box.textContent=`${option.dataset.context||'Address not specified'} · Members: ${members.length ? members.map(m=>m.name+(m.head?' (Head)':'')).join(', ') : 'None'}`;}householdSelect?.addEventListener('change',showHousehold);showHousehold();</script>@endpush
