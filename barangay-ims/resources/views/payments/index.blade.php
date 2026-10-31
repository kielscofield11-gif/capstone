@extends('layouts.app')

@section('title', 'Payments')
@section('header', 'Payments')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-4 md:p-5 border-b border-gray-100">
        <x-filter-bar :route="route('payments.index')" search-placeholder="Search reference, control #, or name..." :filters="[
            ['name' => 'status', 'placeholder' => 'All Statuses', 'options' => ['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'expired' => 'Expired', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded']],
            ['name' => 'provider', 'placeholder' => 'All Providers', 'options' => ['paymongo' => 'PayMongo', 'fake' => 'Demo gateway', 'cash' => 'Cash']],
            ['name' => 'date_from', 'type' => 'date'],
            ['name' => 'date_to', 'type' => 'date'],
        ]" />
    </div>

    <div class="responsive-table" tabindex="0" role="region" aria-label="Payments table">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Document</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Resident</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Method</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Date</th>
                    <th class="text-right px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 md:px-5 py-3">
                            <a href="{{ route('payments.show', $payment) }}" class="font-medium text-gray-900 hover:text-brand-700">{{ $payment->reference_number }}</a>
                            <span class="block mt-1 text-xs text-gray-400">{{ $payment->provider_label }}</span>
                        </td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 break-anywhere">
                            @if($payment->documentRequest)
                                <a href="{{ route('documents.show', $payment->documentRequest) }}" class="font-medium text-gray-900 hover:text-brand-700">{{ $payment->documentRequest->control_number }}</a>
                                <span class="block mt-1 text-xs text-gray-400">{{ $payment->documentRequest->documentType?->name ?? 'N/A' }}</span>
                            @else
                                <span class="text-gray-400">Document removed</span>
                            @endif
                        </td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 break-anywhere hidden md:table-cell">{{ $payment->documentRequest?->resident?->full_name ?? 'Unknown resident' }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 whitespace-nowrap">₱{{ number_format((float) $payment->amount, 2) }}</td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $payment->method_label }}</td>
                        <td class="px-4 md:px-5 py-3"><x-status-badge :status="$payment->status">{{ ucfirst($payment->status) }}</x-status-badge></td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $payment->created_at->format('M d, Y') }}</td>
                        <td class="px-4 md:px-5 py-3 text-right">
                            <a href="{{ route('payments.show', $payment) }}" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" aria-label="View payment {{ $payment->reference_number }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <x-empty-state icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" title="No payments found" message="Payments appear here once a document fee has been paid or a payment link has been created." :colspan="8" />
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">{{ $payments->links() }}</div>
</div>
@endsection
