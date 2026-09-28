<?php

namespace App\Support;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** Tag-based cache for API responses; falls back to no caching on stores without tag support. */
final class ContentCache
{
    /** @param  list<string>  $tags */
    public static function remember(array $tags, string $key, Closure $callback): mixed
    {
        $ttl = (int) config('portfolio.cache_ttl');

        // Only plain arrays are cached: Laravel refuses to unserialize objects (Collections, resources).
        $plain = fn () => json_decode((string) json_encode($callback()), true);

        if (! Cache::supportsTags() || $ttl <= 0) {
            return $plain();
        }

        return Cache::tags($tags)->remember('api:'.$key, $ttl, $plain);
    }

    /** Cache key for a request: path plus its sorted query string. */
    public static function key(Request $request): string
    {
        $query = $request->query();
        ksort($query);

        return $request->path().'?'.http_build_query($query);
    }

    /** @param  list<string>  $tags */
    public static function flush(array $tags): void
    {
        if ($tags !== [] && Cache::supportsTags()) {
            Cache::tags($tags)->flush();
        }
    }
}
