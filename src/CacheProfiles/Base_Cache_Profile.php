<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Cache_Profiles;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
abstract class Base_Cache_Profile implements Cache_Profile
{
    public function enabled(Request $request): bool
    {
        return config('responsecache.enabled');
    }
    public function cache_lifetime_in_seconds(Request $request): int
    {
        return config('responsecache.cache.lifetime_in_seconds');
    }
    public function use_cache_name_suffix(Request $request): string
    {
        return Auth::check() ? (string) Auth::id() : '';
    }
    protected function is_running_in_console(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }
        return app()->running_in_console();
    }
}