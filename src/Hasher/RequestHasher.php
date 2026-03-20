<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Hasher;

use Illuminate\Http\Request;
interface Request_Hasher
{
    public function get_hash_for(Request $request): string;
}