<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

enum Architecture: string
{
    case X86_64 = 'x86_64';
    case Arm64 = 'arm64';
}
