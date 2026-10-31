<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;

class PayMongoWebhookVerifier
{
    /**
     * Verify the Paymongo-Signature header against the raw request body.
     */
    public function verify(Request $request): bool
    {
        $secret = (string) config('payments.paymongo.webhook_secret');

        if ($secret === '') {
            return false;
        }

        $header = trim((string) $request->header('Paymongo-Signature', ''));

        if ($header === '') {
            return false;
        }

        $content = $request->getContent();

        // Some PayMongo documentation describes a plain HMAC of the raw body,
        // while webhook deliveries use the t=/te=/li= header format below.
        // Accept both so signature verification survives either scheme.
        if (! str_contains($header, '=')) {
            return hash_equals(hash_hmac('sha256', $content, $secret), $header);
        }

        $parts = [];

        foreach (explode(',', $header) as $segment) {
            $pair = explode('=', trim($segment), 2);

            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }

        if (! isset($parts['t']) || $parts['t'] === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $parts['t'].'.'.$content, $secret);

        foreach (['te', 'li'] as $key) {
            if (! empty($parts[$key]) && hash_equals($expected, $parts[$key])) {
                return true;
            }
        }

        return false;
    }
}
