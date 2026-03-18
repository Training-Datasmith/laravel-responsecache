<?php

declare(strict_types=1);

namespace Spatie\ResponseCache\Events;

use Illuminate\Http\Request;

class ResponseCacheHitEvent
{
    public function __construct(
        public Request $request,
        public ?int $ageInSeconds = null,
        public ?array $tags = null,
    ) {

    }
}
