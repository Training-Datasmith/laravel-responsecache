<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Exceptions;

use Exception;
class Could_Not_Unserialize extends Exception
{
    public static function serialized_response(string $serialized_response): self
    {
        $truncated = mb_substr($serialized_response, 0, 200);
        if (mb_strlen($serialized_response) > 200) {
            $truncated .= '... (truncated)';
        }
        return new self("Could not unserialize serialized response `{$truncated}`");
    }
}