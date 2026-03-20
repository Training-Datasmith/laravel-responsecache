<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Replacers;

use Symfony\Component\Http_Foundation\Response;
interface Replacer
{
    /*
     * Prepare the initial response before it gets cached.
     */
    public function prepare_response_to_cache(Response $response): void;
    /*
     * Replace any data you want in the cached response before it gets sent.
     */
    public function replace_in_cached_response(Response $response): void;
}