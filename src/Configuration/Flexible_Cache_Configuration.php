<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Configuration;

class Flexible_Cache_Configuration
{
    public function __construct(public int $lifetime, public int $grace, public array $tags = [])
    {
    }
}