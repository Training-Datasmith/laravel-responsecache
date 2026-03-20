<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Attributes;

use Attribute;
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Flexible_Cache
{
    public function __construct(public int $lifetime, public int $grace, public array $tags = [])
    {
    }
}