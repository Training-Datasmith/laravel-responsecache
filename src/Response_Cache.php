<?php

declare (strict_types=1);
namespace Spatie\Response_Cache;

use Closure;
use Illuminate\Http\Request;
use Spatie\Response_Cache\Cache_Item_Selector\Cache_Item_Selector;
use Spatie\Response_Cache\Cache_Profiles\Cache_Profile;
use Spatie\Response_Cache\Concerns\Tagged_Cache_Aware;
use Spatie\Response_Cache\Events\Cleared_Response_Cache_Event;
use Spatie\Response_Cache\Events\Clearing_Response_Cache_Event;
use Spatie\Response_Cache\Events\Clearing_Response_Cache_Failed_Event;
use Spatie\Response_Cache\Hasher\Request_Hasher;
use Symfony\Component\Http_Foundation\Response;
class Response_Cache
{
    use Tagged_Cache_Aware;
    public function __construct(protected Response_Cache_Repository $cache, protected Request_Hasher $hasher, protected Cache_Profile $cache_profile)
    {
    }
    public function enabled(Request $request): bool
    {
        return $this->cache_profile->enabled($request);
    }
    public function should_cache(Request $request, Response $response): bool
    {
        if ($request->attributes->has('responsecache.doNotCache')) {
            return false;
        }
        if (!$this->cache_profile->should_cache_request($request)) {
            return false;
        }
        return $this->cache_profile->should_cache_response($response);
    }
    public function should_bypass(Request $request): bool
    {
        if (!config('responsecache.bypass.header_name')) {
            return false;
        }
        if (!config('responsecache.bypass.header_value')) {
            return false;
        }
        return $request->header(config('responsecache.bypass.header_name')) === (string) config('responsecache.bypass.header_value');
    }
    public function cache_response(Request $request, Response $response, ?int $lifetime_in_seconds = null, array $tags = []): Response
    {
        $this->tagged_cache($tags)->put($this->hasher->get_hash_for($request), $response, $lifetime_in_seconds ?? $this->cache_profile->cache_lifetime_in_seconds($request));
        return $response;
    }
    public function has_been_cached(Request $request, array $tags = []): bool
    {
        return config('responsecache.enabled') && $this->tagged_cache($tags)->has($this->hasher->get_hash_for($request));
    }
    public function get_cached_response_for(Request $request, array $tags = []): Response
    {
        return $this->tagged_cache($tags)->get($this->hasher->get_hash_for($request));
    }
    public function clear(array $tags = []): bool
    {
        event(new Clearing_Response_Cache_Event());
        $result = $this->tagged_cache($tags)->clear();
        $result_event = $result ? new Cleared_Response_Cache_Event() : new Clearing_Response_Cache_Failed_Event();
        event($result_event);
        return $result;
    }
    /**
     * @param  string[]  $tags
     */
    public function forget(string|array $uris, array $tags = []): self
    {
        event(new Clearing_Response_Cache_Event());
        $uris = is_array($uris) ? $uris : [$uris];
        $this->select_cached_items()->for_urls($uris)->using_tags($tags)->forget();
        event(new Cleared_Response_Cache_Event());
        return $this;
    }
    public function select_cached_items(): Cache_Item_Selector
    {
        return new Cache_Item_Selector($this->hasher, $this->cache);
    }
    /**
     * Get a cached response using flexible/SWR (stale-while-revalidate) strategy.
     *
     * @param  array{0: int, 1: int}  $seconds  [fresh_seconds, total_seconds]
     * @param  Closure  $callback  Callback that returns a Response object
     */
    public function flexible(string $key, array $seconds, Closure $callback, array $tags = []): Response
    {
        return $this->tagged_cache($tags)->flexible($key, $seconds, $callback);
    }
}