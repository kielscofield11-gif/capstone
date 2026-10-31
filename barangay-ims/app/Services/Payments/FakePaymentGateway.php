<?php

namespace App\Services\Payments;

use App\Exceptions\PaymentGatewayException;
use App\Models\Payment;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return ! app()->environment('production');
    }

    public function createCheckout(Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new PaymentGatewayException('Demo payments are disabled in production.');
        }

        return [
            'id' => 'cs_fake_'.Str::lower(Str::random(24)),
            'url' => route('payments.demo', [
                'payment' => $payment->id,
                'token' => $payment->return_token,
            ]),
            'payment_intent_id' => 'pi_fake_'.Str::lower(Str::random(24)),
        ];
    }
}
