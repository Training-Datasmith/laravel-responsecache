<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Events;

use Illuminate\Http\Request;
class Response_Cache_Hit_Event
{
    public function __construct(public Request $request, public ?int $age_in_seconds = null, public ?array $tags = null)
    {
    }
}