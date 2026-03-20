<?php

declare(strict_types=1);

/**
 * Example: custom cache profile for laravel-responsecache.
 *
 * Shows how to create a custom Cache_Profile that caches only GET requests
 * for authenticated users with a per-user cache key.
 *
 * Run from the laravel-responsecache project root:
 *   php examples/cache_profile.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

/**
 * A custom cache profile that:
 * - Only caches GET requests
 * - Excludes requests with an Authorization header
 * - Uses the full URL as the cache key
 */
final class PublicOnlyCacheProfile implements CacheProfile
{
    public function enabled(Request $request): bool
    {
        return true; // caching is always enabled
    }

    public function shouldCacheRequest(Request $request): bool
    {
        // Only cache GET requests without Authorization header
        return $request->isMethod('GET')
            && !$request->headers->has('Authorization');
    }

    public function shouldCacheResponse(Response $response): bool
    {
        // Only cache successful (2xx) responses
        return $response->isSuccessful();
    }

    public function cacheRequestUntil(Request $request): \DateTimeInterface
    {
        return Carbon::now()->addMinutes(15);
    }

    public function useCacheNameSuffix(Request $request): string
    {
        return ''; // no per-user suffix for public pages
    }

    public function cacheNameSuffix(Request $request): string
    {
        return '';
    }
}

// --- Demonstrate the profile logic ---
$profile = new PublicOnlyCacheProfile();

$getRequest  = Request::create('/articles', 'GET');
$postRequest = Request::create('/articles', 'POST');
$authRequest = Request::create('/dashboard', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer token']);

printf("GET  /articles  should cache: %s\n", $profile->shouldCacheRequest($getRequest)  ? 'yes' : 'no');
printf("POST /articles  should cache: %s\n", $profile->shouldCacheRequest($postRequest) ? 'yes' : 'no');
printf("GET  /dashboard (auth) cache: %s\n", $profile->shouldCacheRequest($authRequest) ? 'yes' : 'no');

$until = $profile->cacheRequestUntil($getRequest);
printf("Cache until: %s\n", $until->format('Y-m-d H:i:s'));
