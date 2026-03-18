<?php

declare(strict_types=1);

namespace Spatie\ResponseCache\Enums;

enum ResponseType: string
{
    case Normal = 'normal';
    case File = 'file';
}
