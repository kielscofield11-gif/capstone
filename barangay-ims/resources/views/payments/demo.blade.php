@extends('layouts.app')

@section('title', 'Demo Checkout')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-8 px-2">
    <div class="w-full max-w-lg">
        <div class="mb-6 text-center">
            <img src="/images/logo.png" alt="Barangay logo" class="mx-auto h-16 w-16 rounded-full bg-white object-contain p-1 shadow-sm">
            <h1 class="mt-3 text-lg font-bold text-gray-900">{{ config('app.barangay_name') }}</h1>
            <p class="text-sm text-gray-500">Demo payment checkout</p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-amber-900">Demo mode — no real payment</h2>
            <p class="mt-1 text-sm text-amber-800">This page simulates the PayMongo checkout flow for local testing. No money will be charged.</p>

            <dl class="mt-5 space-y-3 border-t border-amber-200 pt-4 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-amber-700">Reference</dt>
                    <dd class="font-medium text-amber-950 break-anywhere text-right">{{ $payment->reference_number }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-amber-700">Document</dt>
                    <dd class="font-medium text-amber-950 text-right">{{ $payment->documentRequest?->control_number ?? 'N/A' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-amber-700">Amount</dt>
                    <dd class="font-medium text-amber-950 text-right">₱{{ number_format((float) $payment->amount, 2) }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-5 space-y-2">
            <form method="POST" action="{{ route('payments.demo.complete', ['payment' => $payment, 'token' => $token]) }}">
                @csrf
                <input type="hidden" name="action" value="pay">
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simulate successful payment</button>
            </form>

            <form method="POST" action="{{ route('payments.demo.complete', ['payment' => $payment, 'token' => $token]) }}">
                @csrf
                <input type="hidden" name="action" value="fail">
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">Simulate failed payment</button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">Disable demo mode by setting PAYMENT_GATEWAY=paymongo and adding your PayMongo keys.</p>
    </div>
</div>
@endsection
