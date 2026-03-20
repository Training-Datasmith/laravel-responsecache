<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Cache_Profiles;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * Abstract base implementing common Cache_Profile behavior.
 *
 * Reads the enabled flag and cache lifetime from the responsecache config file.
 * Appends the authenticated user's ID as the cache name suffix to ensure
 * per-user cache isolation without subclasses needing to handle auth themselves.
 */
abstract class Base_Cache_Profile implements Cache_Profile
{
    /**
     * Determine if response caching is enabled for this request.
     *
     * Reads the `responsecache.enabled` config key.
     *
     * @param  Request $request The incoming HTTP request.
     * @return bool             True if caching is enabled.
     */
    public function enabled(Request $request): bool
    {
        return config('responsecache.enabled');
    }

    /**
     * Return the cache lifetime in seconds for this request.
     *
     * Reads the `responsecache.cache.lifetime_in_seconds` config key.
     *
     * @param  Request $request The incoming HTTP request.
     * @return int              Number of seconds to cache the response.
     */
    public function cache_lifetime_in_seconds(Request $request): int
    {
        return config('responsecache.cache.lifetime_in_seconds');
    }

    /**
     * Return a cache key suffix to scope the cache per authenticated user.
     *
     * Returns the user's ID when authenticated, or an empty string for guests.
     * This prevents leaking private responses between users.
     *
     * @param  Request $request The incoming HTTP request.
     * @return string           The authenticated user's ID, or '' for guests.
     */
    public function use_cache_name_suffix(Request $request): string
    {
        return Auth::check() ? (string) Auth::id() : '';
    }

    /**
     * Determine if the application is running in a console context (not Artisan tests).
     *
     * @return bool True when running as a CLI command outside the test environment.
     */
    protected function is_running_in_console(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }
        return app()->running_in_console();
    }
}