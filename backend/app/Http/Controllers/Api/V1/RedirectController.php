<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /** Where an old public path now lives (404 when there is no redirect). */
    public function show(Request $request): JsonResponse
    {
        $path = '/'.trim((string) $request->query('path'), '/');

        $payload = ContentCache::remember(['redirects'], ContentCache::key($request), function () use ($path) {
            $redirect = Redirect::where('from_path', $path)->first();

            return ['data' => $redirect ? ['to' => $redirect->to_path, 'status' => $redirect->status_code] : null];
        });

        return $payload['data'] === null
            ? response()->json(['message' => 'Not found.'], 404)
            : response()->json($payload);
    }
}
