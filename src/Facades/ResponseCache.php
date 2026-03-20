<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Facades;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Spatie\Response_Cache\Cache_Item_Selector\Cache_Item_Selector;
use Symfony\Component\Http_Foundation\Response;
/**
 * @method static bool clear(array $tags = [])
 * @method static \Spatie\ResponseCache\ResponseCache forget(string|array $uris, array $tags = [])
 * @method static bool enabled(Request $request)
 * @method static bool shouldCache(Request $request, Response $response)
 * @method static bool shouldBypass(Request $request)
 * @method static Response cacheResponse(Request $request, Response $response, ?int $lifetimeInSeconds = null, array $tags = [])
 * @method static bool hasBeenCached(Request $request, array $tags = [])
 * @method static Response getCachedResponseFor(Request $request, array $tags = [])
 * @method static CacheItemSelector selectCachedItems()
 * @method static Response flexible(string $key, array $seconds, Closure $callback, array $tags = [])
 */
class Response_Cache extends Facade
{
    protected static function get_facade_accessor(): string
    {
        return 'responsecache';
    }
}