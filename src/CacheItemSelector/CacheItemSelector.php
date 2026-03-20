<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Cache_Item_Selector;

use Spatie\Response_Cache\Concerns\Tagged_Cache_Aware;
use Spatie\Response_Cache\Hasher\Request_Hasher;
use Spatie\Response_Cache\Response_Cache_Repository;
class Cache_Item_Selector extends Abstract_Request_Builder
{
    use Tagged_Cache_Aware;
    protected array $urls;
    protected array $tags = [];
    public function __construct(protected Request_Hasher $hasher, protected Response_Cache_Repository $cache)
    {
    }
    public function using_tags(string|array $tags): static
    {
        $this->tags = is_array($tags) ? $tags : func_get_args();
        return $this;
    }
    public function for_urls(string|array $urls): static
    {
        $this->urls = is_array($urls) ? $urls : func_get_args();
        return $this;
    }
    public function forget(): void
    {
        collect($this->urls)->map(function (string $uri): string {
            $request = $this->build($uri);
            return $this->hasher->get_hash_for($request);
        })->each(fn(string $hash): bool => $this->tagged_cache($this->tags)->forget($hash));
    }
}