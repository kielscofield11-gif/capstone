@extends('layouts.app')

@section('title', 'New Document Request')
@section('header', 'New Document Request')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('documents.store') }}" class="p-6 md:p-8 space-y-5">
        @csrf

        <x-input-field name="control_number" label="Control Number" :required="true" placeholder="DC-2024-001" value="{{ old('control_number') }}" />

        <x-input-field name="resident_id" label="Resident" type="select" :required="true" value="{{ old('resident_id') }}" :options="['' => 'Select Resident'] + $residents->pluck('full_name', 'id')->toArray()" />

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
            <select name="document_type_id" id="document_type_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('document_type_id') border-red-400 ring-2 ring-red-200 @enderror">
                <option value="">Select Type</option>
                @foreach($documentTypes as $dt)
                    <option value="{{ $dt->id }}" data-fee="{{ $dt->fee_amount }}" {{ old('document_type_id') == $dt->id ? 'selected' : '' }}>
                        {{ $dt->name }} (₱{{ number_format($dt->fee_amount, 2) }})
                    </option>
                @endforeach
            </select>
            @error('document_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee Amount</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                <input type="number" name="fee_amount" id="fee_amount" value="{{ old('fee_amount') }}" step="0.01" min="0"
                    class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
        </div>

        <x-input-field name="purpose" label="Purpose" type="textarea" placeholder="Reason for request..." value="{{ old('purpose') }}" />

        <x-input-field name="remarks" label="Remarks" type="textarea" placeholder="Additional notes..." value="{{ old('remarks') }}" />

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('documents.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Save Request</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('document_type_id')?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const fee = selected?.dataset?.fee || 0;
        document.getElementById('fee_amount').value = fee;
    });
</script>
@endpush
