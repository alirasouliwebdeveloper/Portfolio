<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'turnstile' => [
        'secret' => env('TURNSTILE_SECRET_KEY'),
        'verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
    ],

    'search_console' => [
        'enabled' => (bool) env('GSC_ENABLED', false),
        'site_url' => env('GSC_SITE_URL'),
        'page_base_url' => env('GSC_PAGE_BASE_URL'),
        'credentials_path' => env('GSC_CREDENTIALS_PATH'),
    ],

    // cPanel deploys (DEPLOY_CPANEL.md): the server pulls CI builds from a rolling GitHub release.
    'deploy' => [
        'token' => env('DEPLOY_WEBHOOK_SECRET'),
        'github_repo' => env('DEPLOY_GITHUB_REPO', 'alirasouliwebdeveloper/Portfolio'),
        'github_token' => env('DEPLOY_GITHUB_TOKEN'),
        'release_tag' => env('DEPLOY_RELEASE_TAG', 'deploy-latest'),
        // Absolute path of the Next.js Node.js App root on the same cPanel account.
        'frontend_path' => env('DEPLOY_FRONTEND_PATH'),
    ],

];
