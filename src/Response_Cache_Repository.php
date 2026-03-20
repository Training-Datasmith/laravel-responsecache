<?php

declare (strict_types=1);
namespace Spatie\Response_Cache;

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Cache\Tagged_Cache;
use Spatie\Response_Cache\Serializers\Serializer;
use Symfony\Component\Http_Foundation\Response;
class Response_Cache_Repository
{
    public function __construct(protected Serializer $response_serializer, protected Repository $cache)
    {
    }
    public function put(string $key, Response $response, int $seconds): void
    {
        $this->cache->put($key, $this->response_serializer->serialize($response), now()->add_seconds($seconds));
    }
    /**
     * Get a cached response using flexible/SWR strategy.
     *
     * @param  array{0: int, 1: int}  $seconds  [fresh_seconds, total_seconds]
     * @param  Closure  $callback  Callback that returns a Response object
     */
    public function flexible(string $key, array $seconds, Closure $callback): Response
    {
        $result = $this->cache->flexible($key, $seconds, function () use ($callback): string {
            $response = $callback();
            return $this->response_serializer->serialize($response);
        });
        return $this->response_serializer->unserialize($result);
    }
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }
    public function get(string $key): Response
    {
        return $this->response_serializer->unserialize($this->cache->get($key) ?? '');
    }
    /**
     * If the response cache tag is empty, or a Store doesn't support tags, the whole cache will be cleared.
     *
     * @return bool Whether the cache was cleared successfully.
     */
    public function clear(): bool
    {
        if ($this->is_tagged($this->cache)) {
            return $this->cache->flush();
        }
        if (empty(config('responsecache.cache.tag'))) {
            return $this->cache->clear();
        }
        return $this->cache->tags(config('responsecache.cache.tag'))->flush();
    }
    public function forget(string $key): bool
    {
        return $this->cache->forget($key);
    }
    public function tags(array $tags): self
    {
        if ($this->cache instanceof Tagged_Cache) {
            $tags = array_merge($this->cache->get_tags()->get_names(), $tags);
        }
        return new self($this->response_serializer, $this->cache->tags($tags));
    }
    public function is_tagged(mixed $repository): bool
    {
        return $repository instanceof Tagged_Cache;
    }
}