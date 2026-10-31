<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway
    |--------------------------------------------------------------------------
    |
    | "paymongo" sends the resident to PayMongo's hosted checkout page.
    | "fake" is an offline demo gateway for local testing; it never charges
    | money and is disabled when the application runs in production.
    |
    */

    'default' => env('PAYMENT_GATEWAY', 'paymongo'),

    'currency' => env('PAYMENT_CURRENCY', 'PHP'),

    'paymongo' => [
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v2'),
        'public_key' => env('PAYMONGO_PUBLIC_KEY'),
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'timeout' => (int) env('PAYMONGO_TIMEOUT', 20),

        'payment_method_types' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('PAYMONGO_PAYMENT_METHODS', 'gcash,paymaya,grab_pay,card,qrph'))
        ))),
    ],

];
