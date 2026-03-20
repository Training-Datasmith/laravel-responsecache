<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\Http_Foundation\Response;
class Do_Not_Cache_Response
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->add(['responsecache.doNotCache' => true]);
        return $next($request);
    }
}