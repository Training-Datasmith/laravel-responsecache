<?php

declare(strict_types=1);

namespace Spatie\ResponseCache\Enums;

enum HttpMethod: string
{
    case Get = 'get';
    case Post = 'post';
    case Put = 'put';
    case Patch = 'patch';
    case Delete = 'delete';
    case Head = 'head';
    case Options = 'options';
}
