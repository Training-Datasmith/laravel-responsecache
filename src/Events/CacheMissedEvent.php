<?php

declare(strict_types=1);

namespace Spatie\ResponseCache\Events;

use Illuminate\Http\Request;

class CacheMissedEvent
{
    public function __construct(
        public Request $request,
    ) {

    }
}
