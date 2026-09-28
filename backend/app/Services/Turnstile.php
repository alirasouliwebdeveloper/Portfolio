<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/** Server-side verification of the Cloudflare Turnstile token (never trust the browser). */
class Turnstile
{
    public function verify(?string $token, ?string $ip = null): bool
    {
        $secret = (string) config('services.turnstile.secret');

        // Without a secret the check cannot run: fine on a dev machine, never in production.
        if ($secret === '') {
            return ! app()->isProduction();
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(8)->post(config('services.turnstile.verify_url'), array_filter([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (Throwable) {
            return false;
        }

        return $response->successful() && $response->json('success') === true;
    }
}
