<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Middlewares;

use Carbon\Carbon_Interval;
use Closure;
use Illuminate\Http\Request;
use Spatie\Response_Cache\Attributes\Flexible_Cache;
use Spatie\Response_Cache\Attributes\No_Cache;
use Spatie\Response_Cache\Configuration\Flexible_Cache_Configuration;
use Spatie\Response_Cache\Events\Cache_Missed_Event;
use Spatie\Response_Cache\Events\Response_Cache_Hit_Event;
use Spatie\Response_Cache\Exceptions\Skip_Cache_Exception;
use Spatie\Response_Cache\Hasher\Request_Hasher;
use Spatie\Response_Cache\Replacers\Replacer;
use Spatie\Response_Cache\Response_Cache;
use Symfony\Component\Http_Foundation\Response;
use Throwable;
class Flexible_Cache_Response extends Base_Cache_Middleware
{
    public function __construct(protected Response_Cache $response_cache)
    {
    }
    public function handle(Request $request, Closure $next, ...$args): Response
    {
        $attribute = $this->get_attribute_from_request($request);
        if ($attribute instanceof No_Cache || !$this->response_cache->enabled($request) || $this->response_cache->should_bypass($request)) {
            return $next($request);
        }
        $config = $attribute instanceof Flexible_Cache ? $attribute : $this->get_configuration_from_args($args);
        if (!$config) {
            return $next($request);
        }
        return $this->handle_flexible_cache($request, $next, [$config->lifetime, $config->grace], $config->tags);
    }
    public static function for(int|Carbon_Interval $lifetime, int|Carbon_Interval $grace, string|array $tags = []): string
    {
        $lifetime_seconds = $lifetime instanceof Carbon_Interval ? (int) $lifetime->total_seconds : $lifetime;
        $grace_seconds = $grace instanceof Carbon_Interval ? (int) $grace->total_seconds : $grace;
        $config = new Flexible_Cache_Configuration(lifetime: $lifetime_seconds, grace: $grace_seconds, tags: is_array($tags) ? $tags : [$tags]);
        return static::class . ':' . base64_encode(serialize($config));
    }
    protected function handle_flexible_cache(Request $request, Closure $next, array $flexible_time, array $tags): Response
    {
        $cache_key = app(Request_Hasher::class)->get_hash_for($request);
        $was_miss = false;
        try {
            $response = $this->response_cache->flexible($cache_key, [$flexible_time[0], $flexible_time[1]], function () use ($request, $next, &$was_miss): object {
                $was_miss = true;
                $response = $next($request);
                if (!$this->response_cache->should_cache($request, $response)) {
                    throw new Skip_Cache_Exception($response);
                }
                $cached_response = clone $response;
                $this->add_cache_time_header($cached_response);
                $this->get_replacers()->each(fn(Replacer $replacer) => $replacer->prepare_response_to_cache($cached_response));
                return $cached_response;
            }, $tags);
        } catch (Skip_Cache_Exception $e) {
            event(new Cache_Missed_Event($request));
            return $e->response;
        }
        $this->get_replacers()->each(fn(Replacer $replacer) => $replacer->replace_in_cached_response($response));
        $age_in_seconds = $this->get_age_in_seconds($response);
        $response = $this->add_debug_headers($response, !$was_miss, $cache_key, $age_in_seconds);
        if ($was_miss) {
            event(new Cache_Missed_Event($request));
            return $response;
        }
        event(new Response_Cache_Hit_Event($request, $age_in_seconds, $tags));
        return $response;
    }
    protected function get_configuration_from_args(array $args): ?Flexible_Cache_Configuration
    {
        if (count($args) < 1 || !is_string($args[0])) {
            return null;
        }
        try {
            $decoded = base64_decode($args[0], true);
            if ($decoded === false) {
                return null;
            }
            $config = unserialize($decoded, ['allowed_classes' => [Flexible_Cache_Configuration::class]]);
            return $config instanceof Flexible_Cache_Configuration ? $config : null;
        } catch (Throwable) {
            return null;
        }
    }
}