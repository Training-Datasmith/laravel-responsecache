<?php

declare(strict_types=1);

namespace Spatie\ResponseCache\Configuration;

class FlexibleCacheConfiguration
{
    public function __construct(
        public int $lifetime,
        public int $grace,
        public array $tags = [],
    ) {
    }
}
