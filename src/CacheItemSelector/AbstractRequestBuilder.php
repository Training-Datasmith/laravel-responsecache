<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Cache_Item_Selector;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
abstract class Abstract_Request_Builder
{
    protected string $method = 'GET';
    protected array $parameters = [];
    protected array $cookies = [];
    protected array $server = [];
    protected ?string $cache_name_suffix = null;
    public function with_put_method(): static
    {
        $this->method = 'PUT';
        return $this;
    }
    public function with_patch_method(): static
    {
        $this->method = 'PATCH';
        return $this;
    }
    public function with_post_method(): static
    {
        $this->method = 'POST';
        return $this;
    }
    /**
     * if method is GET then will be converted to query
     * otherwise it will became part of request input
     */
    public function with_parameters(array $parameters): static
    {
        $this->parameters = $parameters;
        return $this;
    }
    public function with_cookies(array $cookies): static
    {
        $this->cookies = $cookies;
        return $this;
    }
    public function with_headers(array $headers): static
    {
        $this->server = collect($this->server)->filter(fn(string $val, string $key): bool => !str_starts_with($key, 'HTTP_'))->merge(collect($headers)->map_with_keys(fn(string $val, string $key) => ['HTTP_' . str_replace('-', '_', Str::upper($key)) => $val]))->to_array();
        return $this;
    }
    public function with_remote_address(string $remote_address): static
    {
        $this->server['REMOTE_ADDR'] = $remote_address;
        return $this;
    }
    public function using_suffix(string $cache_name_suffix): static
    {
        $this->cache_name_suffix = $cache_name_suffix;
        return $this;
    }
    protected function build(string $uri): Request
    {
        $request = Request::create(url($uri), $this->method, $this->parameters, $this->cookies, [], $this->server);
        if (isset($this->cache_name_suffix)) {
            $request->attributes->add(['responsecache.cacheNameSuffix' => $this->cache_name_suffix]);
        }
        return $request;
    }
}