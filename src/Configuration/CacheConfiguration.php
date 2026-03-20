<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Configuration;

class Cache_Configuration
{
    public function __construct(public ?int $lifetime = null, public array $tags = [])
    {
    }
}