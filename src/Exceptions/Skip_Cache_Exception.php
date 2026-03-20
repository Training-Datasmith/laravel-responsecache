<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Exceptions;

use RuntimeException;
use Symfony\Component\Http_Foundation\Response;
class Skip_Cache_Exception extends RuntimeException
{
    public function __construct(public readonly Response $response)
    {
        parent::__construct();
    }
}