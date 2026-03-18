<?php

declare(strict_types=1);

namespace Spatie\ResponseCache\Configuration;

class CacheConfiguration
{
    public function __construct(
        public ?int $lifetime = null,
        public array $tags = [],
    ) {
    }
}
