<?php

declare(strict_types=1);

namespace Spatie\ResponseCache\Hasher;

use Illuminate\Http\Request;

interface RequestHasher
{
    public function getHashFor(Request $request): string;
}
