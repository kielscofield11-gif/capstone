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
        @else
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <h4 class="font-semibold text-gray-900">{{ ucfirst($payment->status) }} payment</h4>
                <p class="mt-2 text-sm text-gray-600">This payment was not completed.</p>
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
