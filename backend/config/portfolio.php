<?php

return [
    'internal_key' => env('API_INTERNAL_KEY'),
    'posts_per_page' => 6,
    'upload' => [
        'types' => ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'zip'],
        'max_mb' => 10,
        'max_files' => 5,
    ],
    'cache_ttl' => (int) env('API_CACHE_TTL', 86400),

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
