<?php

namespace App\Services\Payments;

use App\Exceptions\PaymentException;
use App\Exceptions\PaymentGatewayException;
use App\Models\DocumentRequest;
use App\Models\Payment;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NumberSequenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly AuditLogger $auditLogger,
        private readonly NumberSequenceService $sequences,
    ) {
    }

    public function onlinePaymentsConfigured(): bool
    {
        return $this->gateway->isConfigured();
    }

    public function startOnlinePayment(DocumentRequest $document, User $staff): Payment
    {
        if (! $this->gateway->isConfigured()) {
            throw new PaymentGatewayException('Online payments are not configured. Add PayMongo keys or enable demo mode.');
        }

        if ((float) $document->fee_amount <= 0) {
            throw new PaymentException('This document has no fee to pay.');
        }

        if ($document->status === 'cancelled') {
            throw new PaymentException('Cancelled document requests cannot be paid.');
        }

        if (! in_array($document->status, ['pending', 'approved'], true)) {
            throw new PaymentException('Only pending or approved document requests can generate a payment link.');
        }

        if ($document->isPaid()) {
            throw new PaymentException('This document has already been paid.');
        }

        $existing = $document->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('checkout_url')
            ->where('amount', $document->fee_amount)
            ->latest()
            ->first();

        if ($existing && $existing->created_at?->gt(now()->subHours(23))) {
            return $existing;
        }

        $document->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->where('created_at', '<=', now()->subHours(23))
            ->update([
                'status' => Payment::STATUS_EXPIRED,
                'failure_reason' => 'The payment link expired before it was paid.',
            ]);

        $provider = config('payments.default') === 'fake'
            ? Payment::PROVIDER_FAKE
            : Payment::PROVIDER_PAYMONGO;

        $payment = Payment::create([
            'document_request_id' => $document->id,
            'reference_number' => $this->nextReference(),
            'provider' => $provider,
            'status' => Payment::STATUS_PENDING,
            'amount' => $document->fee_amount,
            'currency' => (string) config('payments.currency', 'PHP'),
            'return_token' => Str::random(64),
            'recorded_by' => $staff->id,
        ]);

        try {
            $checkout = $this->gateway->createCheckout($payment);
        } catch (Throwable $exception) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failure_reason' => 'The payment gateway could not create a checkout session.',
            ]);

            $this->auditLogger->created($payment, "Created payment {$payment->reference_number} for {$document->control_number} (checkout failed)");

            if ($exception instanceof PaymentGatewayException) {
                throw $exception;
            }

            report($exception);

            throw new PaymentGatewayException('Unable to start the online payment. Please try again.', previous: $exception);
        }

        $payment->update([
            'provider_checkout_id' => $checkout['id'],
            'provider_intent_id' => $checkout['payment_intent_id'] ?? null,
            'checkout_url' => $checkout['url'],
            'metadata' => [
                'gateway' => $provider,
                'checkout_created_at' => now()->toIso8601String(),
            ],
        ]);

        $this->auditLogger->created($payment, "Created online payment link {$payment->reference_number} for {$document->control_number}");

        return $payment->refresh();
    }

    public function recordCashPayment(DocumentRequest $document, User $staff): Payment
    {
        if ((float) $document->fee_amount <= 0) {
            throw new PaymentException('This document has no fee to record.');
        }

        if ($document->status === 'cancelled') {
            throw new PaymentException('Cancelled document requests cannot be paid.');
        }

        return DB::transaction(function () use ($document, $staff) {
            $locked = DocumentRequest::whereKey($document->id)->lockForUpdate()->firstOrFail();

            if ($locked->isPaid()) {
                throw new PaymentException('This document has already been paid.');
            }

            $payment = Payment::create([
                'document_request_id' => $locked->id,
                'reference_number' => $this->nextReference(),
                'provider' => Payment::PROVIDER_CASH,
                'status' => Payment::STATUS_PAID,
                'amount' => $locked->fee_amount,
                'currency' => (string) config('payments.currency', 'PHP'),
                'payment_method' => 'cash',
                'return_token' => Str::random(64),
                'paid_at' => now(),
                'recorded_by' => $staff->id,
                'metadata' => ['recorded_by_staff' => $staff->id],
            ]);

            $this->auditLogger->event('payment_recorded', $payment, "Recorded cash payment {$payment->reference_number} for {$locked->control_number}", null, [
                'amount' => (string) $payment->amount,
                'payment_method' => 'cash',
                'status' => Payment::STATUS_PAID,
            ]);

            return $payment;
        });
    }

    public function handleGatewayEvent(string $eventType, array $resource): void
    {
        $payment = $this->locatePayment($resource);

        if (! $payment) {
            Log::warning('PayMongo webhook could not be matched to a payment.', [
                'event_type' => $eventType,
                'resource_id' => $resource['id'] ?? null,
                'reference_number' => data_get($resource, 'attributes.reference_number'),
            ]);

            return;
        }

        if (in_array($eventType, ['checkout_session.payment.paid', 'payment.paid'], true)) {
            $this->applyPaidEvent($payment, $eventType, $resource);

            return;
        }

        if ($eventType === 'payment.failed') {
            $this->markFailed($payment, (string) (data_get($resource, 'attributes.failure_reason')
                ?? data_get($resource, 'attributes.last_payment_error.message')
                ?? 'PayMongo reported the payment as failed.'));

            return;
        }

        Log::info('Ignored PayMongo webhook event.', ['event_type' => $eventType]);
    }

    public function markPaid(Payment $payment, array $data = []): bool
    {
        $updated = DB::transaction(function () use ($payment, $data) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || $locked->isPaid()) {
                return false;
            }

            if (isset($data['amount']) && (int) $data['amount'] < $locked->amountInCentavos()) {
                Log::warning('PayMongo payment amount did not match the expected amount.', [
                    'payment_id' => $locked->id,
                    'expected_centavos' => $locked->amountInCentavos(),
                    'received_centavos' => (int) $data['amount'],
                ]);

                return false;
            }

            $locked->update([
                'status' => Payment::STATUS_PAID,
                'provider_payment_id' => $data['provider_payment_id'] ?? $locked->provider_payment_id,
                'provider_intent_id' => $data['provider_intent_id'] ?? $locked->provider_intent_id,
                'payment_method' => $data['payment_method'] ?? $locked->payment_method,
                'paid_at' => $data['paid_at'] ?? now(),
                'failure_reason' => null,
                'metadata' => array_merge($locked->metadata ?? [], $data['metadata'] ?? []),
            ]);

            return $locked;
        });

        if (! $updated) {
            return false;
        }

        $updated->loadMissing('documentRequest');

        $this->auditLogger->event('payment_paid', $updated, "Payment {$updated->reference_number} confirmed for document request {$updated->documentRequest?->control_number}.", null, [
            'amount' => (string) $updated->amount,
            'payment_method' => $updated->payment_method,
            'provider_payment_id' => $updated->provider_payment_id,
            'status' => Payment::STATUS_PAID,
        ]);

        return true;
    }

    public function markFailed(Payment $payment, ?string $reason = null): bool
    {
        $updated = DB::transaction(function () use ($payment, $reason) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->isPending()) {
                return false;
            }

            $locked->update([
                'status' => Payment::STATUS_FAILED,
                'failure_reason' => Str::limit($reason ?: 'The payment was not completed.', 250),
            ]);

            return $locked;
        });

        if (! $updated) {
            return false;
        }

        $updated->loadMissing('documentRequest');

        $this->auditLogger->event('payment_failed', $updated, "Payment {$updated->reference_number} failed for document request {$updated->documentRequest?->control_number}.", null, [
            'failure_reason' => $updated->failure_reason,
            'status' => Payment::STATUS_FAILED,
        ]);

        return true;
    }

    private function applyPaidEvent(Payment $payment, string $eventType, array $resource): void
    {
        $attributes = $resource['attributes'] ?? [];
        $sessionPayments = $attributes['payments'] ?? [];
        $paidResource = collect($sessionPayments)->first(
            fn ($item) => data_get($item, 'attributes.status') === Payment::STATUS_PAID
        ) ?? ($sessionPayments[0] ?? null);

        $amount = data_get($paidResource, 'attributes.amount')
            ?? data_get($attributes, 'payment_intent.attributes.amount')
            ?? data_get($attributes, 'amount');

        $paidAt = data_get($paidResource, 'attributes.paid_at') ?? data_get($attributes, 'paid_at');

        $this->markPaid($payment, [
            'amount' => $amount,
            'provider_payment_id' => data_get($paidResource, 'id')
                ?? (($resource['type'] ?? null) === 'payment' ? ($resource['id'] ?? null) : null),
            'provider_intent_id' => data_get($attributes, 'payment_intent.id')
                ?? data_get($paidResource, 'attributes.payment_intent_id')
                ?? data_get($attributes, 'payment_intent_id'),
            'payment_method' => data_get($paidResource, 'attributes.source.type')
                ?? data_get($attributes, 'source.type'),
            'paid_at' => $paidAt ? Carbon::createFromTimestamp((int) $paidAt) : now(),
            'metadata' => [
                'gateway_event' => $eventType,
                'provider_payment_id' => data_get($paidResource, 'id'),
                'payment_method' => data_get($paidResource, 'attributes.source.type')
                    ?? data_get($attributes, 'source.type'),
            ],
        ]);
    }

    private function locatePayment(array $resource): ?Payment
    {
        $metadata = data_get($resource, 'attributes.metadata', []);
        $paymentId = data_get($metadata, 'payment_id');

        if ($paymentId) {
            $payment = Payment::find($paymentId);

            if ($payment) {
                return $payment;
            }
        }

        $reference = data_get($metadata, 'reference_number')
            ?? data_get($resource, 'attributes.reference_number');

        if ($reference) {
            $payment = Payment::where('reference_number', $reference)->first();

            if ($payment) {
                return $payment;
            }
        }

        $intentId = data_get($resource, 'attributes.payment_intent_id')
            ?? data_get($resource, 'attributes.payment_intent.id');

        if ($intentId) {
            $payment = Payment::where('provider_intent_id', $intentId)->first();

            if ($payment) {
                return $payment;
            }
        }

        $resourceId = (string) ($resource['id'] ?? '');

        if (str_starts_with($resourceId, 'cs_')) {
            return Payment::where('provider_checkout_id', $resourceId)->first();
        }

        if (str_starts_with($resourceId, 'pay_')) {
            return Payment::where('provider_payment_id', $resourceId)->first();
        }

        return null;
    }

    private function nextReference(): string
    {
        $year = now()->year;

        return $this->sequences->nextFormatted('document_payment', 'PAY', $year, function () use ($year) {
            $maximum = 0;

            foreach (Payment::query()->whereYear('created_at', $year)->pluck('reference_number') as $reference) {
                if (preg_match('/^PAY-'.$year.'-(\d+)$/', (string) $reference, $matches)) {
                    $maximum = max($maximum, (int) $matches[1]);
                }
            }

            return $maximum;
        });
    }
}
