<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Only the Next.js server (which knows the shared secret) may read the API. */
class InternalKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('portfolio.internal_key');
        $given = (string) $request->header('X-Internal-Key', '');

        if ($expected === '' || ! hash_equals($expected, $given)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
