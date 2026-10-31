<?php

return [
    // Seed credentials are read through config so `php artisan config:cache`
    // does not turn them into null values (env() returns null once the
    // configuration is cached).
    'mock_data' => env('SEED_MOCK_DATA', false),
    'allow_mock_data' => env('ALLOW_MOCK_DATA_SEEDING', false),

    'passwords' => [
        'admin' => env('SEED_ADMIN_PASSWORD'),
        'secretary' => env('SEED_SECRETARY_PASSWORD'),
        'captain' => env('SEED_CAPTAIN_PASSWORD'),
        'kagawad' => env('SEED_KAGAWAD_PASSWORD'),
        'staff' => env('SEED_STAFF_PASSWORD'),
    ],
];
