<?php

/*
 * Docker injects the development .env into the real process environment, and PHP exposes it
 * in $_SERVER, which Laravel reads before PHPUnit's <env> values. Set the test environment in
 * every place before Laravel boots so tests can never touch the development database.
 */
$testEnv = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'BROADCAST_CONNECTION' => 'null',
    'QUEUE_CONVERSIONS_BY_DEFAULT' => 'false',
    'TURNSTILE_SECRET_KEY' => '',
    'GSC_ENABLED' => 'false',
    'API_INTERNAL_KEY' => 'test-key',
    'MAIL_TO_ADDRESS' => 'owner@example.com',
    'ADMIN_EMAIL' => 'admin@example.com',
    'ADMIN_PASSWORD' => 'test-password',
];

foreach ($testEnv as $key => $value) {
    $_SERVER[$key] = $_ENV[$key] = $value;
    putenv("{$key}={$value}");
}

require __DIR__.'/../vendor/autoload.php';
