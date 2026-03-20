<?php

declare (strict_types=1);
namespace Spatie\Response_Cache;

use Illuminate\Cache\Repository;
use Illuminate\Container\Container;
use Spatie\Laravel_Package_Tools\Package;
use Spatie\Laravel_Package_Tools\Package_Service_Provider;
use Spatie\Response_Cache\Cache_Profiles\Cache_Profile;
use Spatie\Response_Cache\Commands\Clear_Command;
use Spatie\Response_Cache\Hasher\Request_Hasher;
use Spatie\Response_Cache\Middlewares\Cache_Response;
use Spatie\Response_Cache\Serializers\Serializer;
class Response_Cache_Service_Provider extends Package_Service_Provider
{
    public function configure_package(Package $package): void
    {
        $package->name('laravel-responsecache')->has_config_file()->has_commands([Clear_Command::class]);
    }
    public function package_booted(): void
    {
        $this->app->singleton(Cache_Response::class);
        $this->app->bind(Cache_Profile::class, fn(Container $app) => $app->make(config('responsecache.cache_profile')));
        $this->app->bind(Request_Hasher::class, fn(Container $app) => $app->make(config('responsecache.hasher')));
        $this->app->bind(Serializer::class, fn(Container $app) => $app->make(config('responsecache.serializer')));
        $this->app->when(Response_Cache_Repository::class)->needs(Repository::class)->give(function (): Repository {
            /** @var Repository $repository */
            $repository = app('cache')->store(config('responsecache.cache.store'));
            if (!empty(config('responsecache.cache.tag'))) {
                /** @var Repository */
                return $repository->tags(config('responsecache.cache.tag'));
            }
            return $repository;
        });
        $this->app->singleton('responsecache', Response_Cache::class);
    }
}