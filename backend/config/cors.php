<?php

/*
 * Only the public upload endpoint is called by the browser (for real upload progress), so CORS is
 * opened for it alone and only for the frontend origin. Everything else is server-to-server.
 */
return [
    'paths' => ['api/v1/uploads', 'api/v1/uploads/*'],
    'allowed_methods' => ['POST', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_filter([env('FRONTEND_URL', 'http://localhost:3000')]),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Accept', 'X-Requested-With'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => false,
];
