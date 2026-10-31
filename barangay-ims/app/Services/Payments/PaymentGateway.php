<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    public function isConfigured(): bool;

    /**
     * Create a hosted checkout session for the given payment.
     *
     * @return array{id: string, url: string, payment_intent_id: ?string}
     */
    public function createCheckout(Payment $payment): array;
}
