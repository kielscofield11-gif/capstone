<?php

namespace App\Services\Payments;

use App\Exceptions\PaymentGatewayException;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        $secret = (string) config('payments.paymongo.secret_key');

        return $secret !== '' && str_starts_with($secret, 'sk_');
    }

    public function createCheckout(Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new PaymentGatewayException('PayMongo is not configured. Add your API keys before creating a payment link.');
        }

        $payment->loadMissing(['documentRequest.documentType']);

        $baseUrl = rtrim((string) config('payments.paymongo.base_url', 'https://api.paymongo.com/v2'), '/');
        $document = $payment->documentRequest;
        $documentType = $document?->documentType?->name ?? 'Barangay document';

        $response = Http::withBasicAuth((string) config('payments.paymongo.secret_key'), '')
            ->withHeaders(['Idempotency-Key' => 'checkout-'.$payment->reference_number])
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('payments.paymongo.timeout', 20))
            ->post($baseUrl.'/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'line_items' => [[
                            'name' => 'Barangay fee - '.$documentType,
                            'description' => $document
                                ? 'Document fee for '.$document->control_number
                                : 'Barangay document fee',
                            'amount' => $payment->amountInCentavos(),
                            'currency' => $payment->currency,
                            'quantity' => 1,
                        ]],
                        'payment_method_types' => config('payments.paymongo.payment_method_types', ['gcash', 'paymaya', 'grab_pay', 'card', 'qrph']),
                        'success_url' => route('payments.return', [
                            'payment' => $payment->id,
                            'token' => $payment->return_token,
                        ]),
                        'cancel_url' => route('payments.cancel', [
                            'payment' => $payment->id,
                            'token' => $payment->return_token,
                        ]),
                        'reference_number' => $payment->reference_number,
                        'description' => $document
                            ? 'Barangay document request '.$document->control_number
                            : 'Barangay document fee',
                        'metadata' => [
                            'payment_id' => (string) $payment->id,
                            'reference_number' => $payment->reference_number,
                            'document_request_id' => (string) $payment->document_request_id,
                            'control_number' => (string) ($document?->control_number ?? ''),
                        ],
                        'send_email_receipt' => false,
                        'show_description' => true,
                        'show_line_items' => true,
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::warning('PayMongo checkout session creation failed.', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw new PaymentGatewayException('PayMongo could not create the payment link. Please verify the gateway configuration.');
        }

        $checkoutId = (string) $response->json('data.id');
        $checkoutUrl = (string) ($response->json('data.attributes.checkout_url') ?? $response->json('data.attributes.url'));

        if ($checkoutId === '' || $checkoutUrl === '') {
            Log::warning('PayMongo returned an incomplete checkout session response.', [
                'body' => $response->json(),
            ]);

            throw new PaymentGatewayException('PayMongo returned an incomplete payment link response.');
        }

        return [
            'id' => $checkoutId,
            'url' => $checkoutUrl,
            'payment_intent_id' => $response->json('data.attributes.payment_intent.id')
                ?? $response->json('data.attributes.payment_intent_id'),
        ];
    }
}
