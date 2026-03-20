<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Concerns;

use Spatie\Response_Cache\Response_Cache_Repository;
trait Tagged_Cache_Aware
{
    protected function tagged_cache(array $tags = []): Response_Cache_Repository
    {
        if (empty($tags)) {
            return $this->cache;
        }
        return $this->cache->tags($tags);
    }
}