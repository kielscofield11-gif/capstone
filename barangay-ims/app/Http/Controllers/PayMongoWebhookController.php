<?php

namespace App\Http\Controllers;

use App\Services\Payments\PayMongoWebhookVerifier;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __invoke(Request $request, PayMongoWebhookVerifier $verifier, PaymentService $payments)
    {
        if (! $verifier->verify($request)) {
            Log::warning('Rejected a PayMongo webhook with an invalid signature.', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $eventType = (string) $request->input('data.attributes.type', '');
        $resource = $request->input('data.attributes.data');

        if ($eventType === '' || ! is_array($resource)) {
            return response()->json(['received' => true]);
        }

        $payments->handleGatewayEvent($eventType, $resource);

        return response()->json(['received' => true]);
    }
}
