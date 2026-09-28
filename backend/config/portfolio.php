<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    'seed_path' => env('SEED_PATH', base_path('../seed')),
    'assets_path' => env('ASSETS_PATH', base_path('../assets/images')),

    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD'),
        'require_2fa' => (bool) env('ADMIN_2FA_REQUIRED', false),
    ],
];
