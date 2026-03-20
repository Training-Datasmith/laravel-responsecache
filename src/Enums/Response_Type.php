<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Enums;

enum Response_Type : string
{
    case Normal = 'normal';
    case File = 'file';
}