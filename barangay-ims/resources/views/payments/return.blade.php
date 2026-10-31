@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
@php
    $isPaid = $payment->isPaid();
    $state = $isPaid ? 'paid' : ($cancelled ? 'cancelled' : $payment->status);
    $states = [
        'paid' => [
            'wrap' => 'border-emerald-200 bg-emerald-50',
            'iconWrap' => 'bg-emerald-100 text-emerald-700',
            'title' => 'Payment received',
            'message' => 'Thank you. Your payment has been confirmed and the barangay can now process your document.',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'pending' => [
            'wrap' => 'border-amber-200 bg-amber-50',
            'iconWrap' => 'bg-amber-100 text-amber-700',
            'title' => 'Waiting for confirmation',
            'message' => 'If you have just paid, confirmation may take a few moments. Refresh this page to check again.',
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'failed' => [
            'wrap' => 'border-red-200 bg-red-50',
            'iconWrap' => 'bg-red-100 text-red-700',
            'title' => 'Payment did not go through',
            'message' => 'The payment was not completed. Please contact the barangay hall or ask staff for a new payment link.',
            'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'cancelled' => [
            'wrap' => 'border-gray-200 bg-gray-50',
            'iconWrap' => 'bg-gray-100 text-gray-600',
            'title' => 'Checkout cancelled',
            'message' => 'No payment was completed. If you still need to pay, you can reopen the payment link below.',
            'icon' => 'M6 18L18 6M6 6l12 12',
        ],
        'expired' => [
            'wrap' => 'border-gray-200 bg-gray-50',
            'iconWrap' => 'bg-gray-100 text-gray-600',
            'title' => 'Payment link expired',
            'message' => 'This payment link is no longer active. Please ask barangay staff to create a new one.',
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'refunded' => [
            'wrap' => 'border-purple-200 bg-purple-50',
            'iconWrap' => 'bg-purple-100 text-purple-700',
            'title' => 'Payment refunded',
            'message' => 'This payment has been refunded. Please contact the barangay hall if you have questions.',
            'icon' => 'M3 10h10a4 4 0 010 8H9m-6-8l4-4m-4 4l4 4',
        ],
    ];
    $current = $states[$state] ?? $states['pending'];
@endphp

<div class="min-h-[70vh] flex items-center justify-center py-8 px-2">
    <div class="w-full max-w-lg">
        <div class="mb-6 text-center">
            <img src="/images/logo.png" alt="Barangay logo" class="mx-auto h-16 w-16 rounded-full bg-white object-contain p-1 shadow-sm">
            <h1 class="mt-3 text-lg font-bold text-gray-900">{{ config('app.barangay_name') }}</h1>
            <p class="text-sm text-gray-500">Online payment status</p>
        </div>

        <div class="rounded-2xl border {{ $current['wrap'] }} p-6 shadow-sm">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $current['iconWrap'] }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $current['icon'] }}"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $current['title'] }}</h2>
                    <p class="mt-1 text-sm text-gray-700">{{ $current['message'] }}</p>
                </div>
            </div>

            <dl class="mt-6 space-y-3 border-t border-black/5 pt-4 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500">Reference</dt>
                    <dd class="font-medium text-gray-900 break-anywhere text-right">{{ $payment->reference_number }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500">Document</dt>
                    <dd class="font-medium text-gray-900 text-right">{{ $payment->documentRequest?->control_number ?? 'N/A' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500">Amount</dt>
                    <dd class="font-medium text-gray-900 text-right">₱{{ number_format((float) $payment->amount, 2) }}</dd>
                </div>
                @if($isPaid)
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-gray-500">Paid via</dt>
                        <dd class="font-medium text-gray-900 text-right">{{ $payment->method_label }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-gray-500">Paid at</dt>
                        <dd class="font-medium text-gray-900 text-right">{{ $payment->paid_at?->format('M d, Y h:i A') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
            @if($state === 'pending')
                <a href="{{ route('payments.return', ['payment' => $payment, 'token' => $token]) }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg bg-brand-800 px-4 py-2 text-sm font-medium text-white hover:bg-brand-900">Refresh status</a>
            @endif

            @if(in_array($state, ['cancelled', 'pending', 'failed'], true) && $payment->checkout_url)
                <a href="{{ $payment->checkout_url }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">
                    {{ $state === 'cancelled' ? 'Reopen payment page' : 'Open payment page' }}
                </a>
            @endif

            @if($state === 'paid')
                <a href="{{ route('login') }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">Barangay staff login</a>
            @endif
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">Keep your reference number for follow-up at the barangay hall.</p>
    </div>
</div>
@endsection
