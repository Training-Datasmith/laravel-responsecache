<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Middlewares;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Response_Cache\Attributes\Cache;
use Spatie\Response_Cache\Attributes\Flexible_Cache;
use Spatie\Response_Cache\Attributes\No_Cache;
use Spatie\Response_Cache\Support\Attribute_Reader;
use Symfony\Component\Http_Foundation\Response;
abstract class Base_Cache_Middleware
{
    protected function get_replacers(): Collection
    {
        return collect(config('responsecache.replacers'))->map(fn(string $replacer_class) => app($replacer_class));
    }
    protected function get_attribute_from_request(Request $request): Cache|Flexible_Cache|No_Cache|null
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }
        $action = $route->get_action('controller');
        if (!$action) {
            return null;
        }
        return Attribute_Reader::get_first_attribute($action, [Cache::class, Flexible_Cache::class, No_Cache::class]);
    }
    protected function add_debug_headers(Response $response, bool $is_hit, string $cache_key, ?int $age_in_seconds = null): Response
    {
        if (!config('responsecache.debug.enabled')) {
            return $response;
        }
        $response->headers->set(config('responsecache.debug.cache_status_header_name'), $is_hit ? 'HIT' : 'MISS');
        if ($is_hit && $age_in_seconds !== null) {
            $response->headers->set(config('responsecache.debug.cache_age_header_name'), (string) $age_in_seconds);
        }
        if (config('app.debug')) {
            $response->headers->set(config('responsecache.debug.cache_key_header_name'), $cache_key);
        }
        return $response;
    }
    protected function add_cache_time_header(Response $response): void
    {
        if (config('responsecache.debug.enabled')) {
            $response->headers->set(config('responsecache.debug.cache_time_header_name'), Carbon::now()->to_rfc2822string());
        }
    }
    protected function get_age_in_seconds(Response $response): ?int
    {
        $time = $response->headers->get(config('responsecache.debug.cache_time_header_name'));
        if (!$time) {
            return null;
        }
        return (int) Carbon::parse($time)->diff_in_seconds(Carbon::now(), true);
    }
}