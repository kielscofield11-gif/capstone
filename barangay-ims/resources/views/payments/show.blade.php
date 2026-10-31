@extends('layouts.app')

@section('title', 'Payment ' . $payment->reference_number)
@section('header', 'Payment ' . $payment->reference_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">Payment Details</h3>
                <x-status-badge :status="$payment->status">{{ ucfirst($payment->status) }}</x-status-badge>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 min-[390px]:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm break-anywhere">
                    <div>
                        <dt class="text-gray-500 text-xs">Reference Number</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->reference_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Document</dt>
                        <dd>
                            @if($payment->documentRequest)
                                <a href="{{ route('documents.show', $payment->documentRequest) }}" class="font-medium text-brand-600 hover:text-brand-700">{{ $payment->documentRequest->control_number }}</a>
                            @else
                                <span class="font-medium text-gray-500">Document removed</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Resident</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->documentRequest?->resident?->full_name ?? 'Unknown resident' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Document Type</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->documentRequest?->documentType?->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Amount</dt>
                        <dd class="font-medium text-gray-900">₱{{ number_format((float) $payment->amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Provider</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->provider_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Payment Method</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->method_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Created</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->created_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    @if($payment->paid_at)
                    <div>
                        <dt class="text-gray-500 text-xs">Paid At</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->paid_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    @endif
                </dl>

                @if($payment->failure_reason)
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <p class="font-medium">Payment issue</p>
                        <p class="mt-1">{{ $payment->failure_reason }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($payment->isPending() && $payment->checkout_url)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-900">Share the payment link</h3>
                <p class="mt-1 text-sm text-gray-500">Send this link to the resident. PayMongo confirms the payment automatically through a signed webhook.</p>

                <label for="payment-link" class="mt-4 block text-xs font-medium text-gray-500">Payment link</label>
                <div class="mt-1 flex flex-col sm:flex-row gap-2">
                    <input id="payment-link" type="text" readonly value="{{ $payment->checkout_url }}"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <button id="copy-payment-link" type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copy link
                    </button>
                </div>

                <div class="mt-3 flex flex-col sm:flex-row gap-2">
                    <a href="{{ $payment->checkout_url }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">
                        Open checkout
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a href="{{ route('documents.show', $payment->documentRequest) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">Back to document</a>
                </div>

                <p class="mt-3 text-xs text-gray-400">Only share this link with the resident. The link is valid for up to 24 hours.</p>

                @if($payment->provider === \App\Models\Payment::PROVIDER_FAKE)
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        <strong>Demo mode:</strong> this checkout page only simulates a payment. No real money is charged.
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="space-y-4">
        @if($payment->isPaid())
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                <div class="flex items-center gap-2 text-emerald-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h4 class="font-semibold">Payment confirmed</h4>
                </div>
                <p class="mt-2 text-sm text-emerald-700">This document request can now be released.</p>
            </div>
        @elseif($payment->isPending())
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                <h4 class="font-semibold text-amber-900">Waiting for payment</h4>
                <p class="mt-2 text-sm text-amber-700">The payment link is active. This page will show the confirmed status after PayMongo sends its webhook.</p>
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <h4 class="font-semibold text-gray-900">{{ ucfirst($payment->status) }} payment</h4>
                <p class="mt-2 text-sm text-gray-600">This payment did not go through. Create a new payment link from the document page if the resident still needs to pay.</p>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Recorded By</h4>
            <p class="text-sm text-gray-900">{{ $payment->recordedBy?->name ?? 'System' }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $payment->created_at->format('M d, Y h:i A') }}</p>
        </div>

        <a href="{{ route('payments.index') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">All payments</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('copy-payment-link')?.addEventListener('click', async function () {
        const input = document.getElementById('payment-link');
        if (!input) return;

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(input.value);
            } else {
                input.select();
                document.execCommand('copy');
            }

            const label = this.innerHTML;
            this.innerHTML = 'Copied!';
            setTimeout(() => { this.innerHTML = label; }, 2000);
        } catch (error) {
            input.select();
        }
    });
</script>
@endpush
