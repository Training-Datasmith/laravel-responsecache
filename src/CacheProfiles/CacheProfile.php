<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Cache_Profiles;

use Illuminate\Http\Request;
use Symfony\Component\Http_Foundation\Response;
interface Cache_Profile
{
    public function enabled(Request $request): bool;
    public function should_cache_request(Request $request): bool;
    public function should_cache_response(Response $response): bool;
    public function cache_lifetime_in_seconds(Request $request): int;
    /*
     * Return a string to differentiate this request from others.
     *
     * For example: if you want a different cache per user you could return the id of
     * the logged in user.
     */
    public function use_cache_name_suffix(Request $request): string;
}