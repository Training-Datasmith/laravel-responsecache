<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Serializers;

use Symfony\Component\Http_Foundation\Response;
interface Serializer
{
    public function serialize(Response $response): string;
    public function unserialize(string $serialized_response): Response;
}