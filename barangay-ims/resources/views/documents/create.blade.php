@extends('layouts.app')

@section('title', 'New Document Request')
@section('header', 'New Document Request')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('documents.store') }}" class="p-6 md:p-8 space-y-5">
        @csrf

        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800"><strong>Control Number:</strong> Automatically generated upon saving.</div>

        <x-input-field name="resident_id" label="Resident" type="select" :required="true" value="{{ old('resident_id') }}" :options="['' => 'Select Resident'] + $residents->pluck('full_name', 'id')->toArray()" />

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
            <select name="document_type_id" id="document_type_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('document_type_id') border-red-400 ring-2 ring-red-200 @enderror">
                <option value="">Select Type</option>
                @foreach($documentTypes as $dt)
                    <option value="{{ $dt->id }}" data-fee="{{ $dt->fee_amount }}" data-requirements="{{ $dt->requirements }}" data-days="{{ $dt->processing_days }}" {{ old('document_type_id') == $dt->id ? 'selected' : '' }}>
                        {{ $dt->name }} (₱{{ number_format($dt->fee_amount, 2) }})
                    </option>
                @endforeach
            </select>
            @error('document_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div id="type-information" class="hidden rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
            <p class="font-semibold text-gray-900" id="type-processing"></p>
            <div class="mt-2"><span class="font-medium text-gray-700">Requirements</span><div id="type-requirements" class="mt-1 whitespace-pre-line text-gray-600"></div></div>
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

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-4 border-t border-gray-100 [&>*]:w-full sm:[&>*]:w-auto [&>*]:min-h-11">
            <a href="{{ route('documents.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Save Request</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    const typeSelect = document.getElementById('document_type_id');
    function updateTypeInformation() {
        const selected = typeSelect?.options[typeSelect.selectedIndex];
        const panel = document.getElementById('type-information');
        if (!selected?.value) { panel?.classList.add('hidden'); return; }
        panel.classList.remove('hidden');
        document.getElementById('type-processing').textContent = selected.dataset.days ? `Estimated Processing Time: ${selected.dataset.days} day(s)` : 'Processing time not specified';
        document.getElementById('type-requirements').textContent = selected.dataset.requirements || 'No requirements specified.';
    }
    typeSelect?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const fee = selected?.dataset?.fee || 0;
        document.getElementById('fee_amount').value = fee;
        updateTypeInformation();
    });
    updateTypeInformation();
</script>
@endpush
