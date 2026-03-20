<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Events;

use Illuminate\Http\Request;
class Cache_Missed_Event
{
    public function __construct(public Request $request)
    {
    }
}