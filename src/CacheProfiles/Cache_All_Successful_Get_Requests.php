<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Cache_Profiles;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Response_Cache\Enums\Http_Method;
use Symfony\Component\Http_Foundation\Response;
class Cache_All_Successful_Get_Requests extends Base_Cache_Profile
{
    public function should_cache_request(Request $request): bool
    {
        if ($request->ajax()) {
            return false;
        }
        if ($this->is_running_in_console()) {
            return false;
        }
        return $request->is_method(Http_Method::Get->value);
    }
    public function should_cache_response(Response $response): bool
    {
        if (!$this->has_cacheable_response_code($response)) {
            return false;
        }
        if (!$this->has_cacheable_content_type($response)) {
            return false;
        }
        return true;
    }
    public function has_cacheable_response_code(Response $response): bool
    {
        if ($response->is_successful()) {
            return true;
        }
        if ($response->is_redirection()) {
            return true;
        }
        return false;
    }
    public function has_cacheable_content_type(Response $response): bool
    {
        $content_type = $response->headers->get('Content-Type', '');
        if (str_starts_with((string) $content_type, 'text/')) {
            return true;
        }
        if (Str::contains($content_type, ['/json', '+json'])) {
            return true;
        }
        return false;
    }
}