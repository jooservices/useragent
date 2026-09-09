<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

enum DeviceClass: string
{
    case Desktop = 'desktop';
    case Mobile = 'mobile';
    case Tablet = 'tablet';
}
