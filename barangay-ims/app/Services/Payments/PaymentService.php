<?php

namespace App\Services\Payments;

use App\Exceptions\PaymentException;
use App\Models\DocumentRequest;
use App\Models\Payment;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NumberSequenceService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly NumberSequenceService $sequences,
    ) {
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
