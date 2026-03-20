<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Middlewares;

use Carbon\Carbon_Interval;
use Closure;
use Illuminate\Http\Request;
use Spatie\Response_Cache\Attributes\Cache;
use Spatie\Response_Cache\Attributes\Flexible_Cache;
use Spatie\Response_Cache\Attributes\No_Cache;
use Spatie\Response_Cache\Configuration\Cache_Configuration;
use Spatie\Response_Cache\Events\Cache_Missed_Event;
use Spatie\Response_Cache\Events\Response_Cache_Hit_Event;
use Spatie\Response_Cache\Hasher\Request_Hasher;
use Spatie\Response_Cache\Replacers\Replacer;
use Spatie\Response_Cache\Response_Cache;
use Symfony\Component\Http_Foundation\Response;
use Throwable;
class Cache_Response extends Base_Cache_Middleware
{
    private bool $should_cache = false;
    private ?int $pending_lifetime = null;
    /** @var string[] */
    private array $pending_tags = [];
    public function __construct(protected Response_Cache $response_cache)
    {
    }
    public static function for(int|Carbon_Interval|null $lifetime = null, string|array $tags = []): string
    {
        $lifetime_in_seconds = $lifetime instanceof Carbon_Interval ? (int) $lifetime->total_seconds : $lifetime;
        $config = new Cache_Configuration(lifetime: $lifetime_in_seconds, tags: is_array($tags) ? $tags : [$tags]);
        return static::class . ':' . base64_encode(serialize($config));
    }
    public function handle(Request $request, Closure $next, ...$args): Response
    {
        $this->should_cache = false;
        $this->pending_lifetime = null;
        $this->pending_tags = [];
        $attribute = $this->get_attribute_from_request($request);
        if ($attribute instanceof No_Cache) {
            return $next($request);
        }
        if ($attribute instanceof Flexible_Cache) {
            return app(Flexible_Cache_Response::class)->handle($request, $next);
        }
        if (!$this->response_cache->enabled($request) || $this->response_cache->should_bypass($request)) {
            return $next($request);
        }
        $config = $attribute instanceof Cache ? $attribute : $this->get_configuration_from_args($args);
        $lifetime_in_seconds = $config?->lifetime;
        $tags = $config?->tags ?? [];
        if ($cached_response = $this->get_cached_response($request, $tags)) {
            return $cached_response;
        }
        $response = $next($request);
        if ($this->response_cache->should_cache($request, $response)) {
            $this->should_cache = true;
            $this->pending_lifetime = $lifetime_in_seconds;
            $this->pending_tags = $tags;
        }
        $cache_key = app(Request_Hasher::class)->get_hash_for($request);
        $response = $this->add_debug_headers($response, false, $cache_key);
        event(new Cache_Missed_Event($request));
        return $response;
    }
    public function terminate(Request $request, Response $response): void
    {
        if (!$this->should_cache) {
            return;
        }
        $this->cache_response($request, $response, $this->pending_lifetime, $this->pending_tags);
    }
    protected function get_cached_response(Request $request, array $tags): ?Response
    {
        if (!$this->response_cache->has_been_cached($request, $tags)) {
            return null;
        }
        $cache_key = app(Request_Hasher::class)->get_hash_for($request);
        try {
            $response = $this->response_cache->get_cached_response_for($request, $tags);
        } catch (Throwable $exception) {
            report("Could not serve cached response: {$exception->get_message()}");
            return null;
        }
        $age_in_seconds = $this->get_age_in_seconds($response);
        event(new Response_Cache_Hit_Event($request, $age_in_seconds, $tags));
        $response = $this->add_debug_headers($response, true, $cache_key, $age_in_seconds);
        $this->get_replacers()->each(fn(Replacer $replacer) => $replacer->replace_in_cached_response($response));
        return $response;
    }
    protected function cache_response(Request $request, Response $response, ?int $lifetime_in_seconds, array $tags): void
    {
        $cached_response = clone $response;
        $cached_response->headers->remove(config('responsecache.debug.cache_status_header_name'));
        $this->add_cache_time_header($cached_response);
        $this->get_replacers()->each(fn(Replacer $replacer) => $replacer->prepare_response_to_cache($cached_response));
        $this->response_cache->cache_response($request, $cached_response, $lifetime_in_seconds, $tags);
    }
    protected function get_configuration_from_args(array $args): ?Cache_Configuration
    {
        if (!isset($args[0]) || !is_string($args[0])) {
            return null;
        }
        try {
            $decoded = base64_decode($args[0], true);
            if ($decoded === false) {
                return null;
            }
            $config = unserialize($decoded, ['allowed_classes' => [Cache_Configuration::class]]);
            return $config instanceof Cache_Configuration ? $config : null;
        } catch (Throwable) {
            return null;
        }
    }
}