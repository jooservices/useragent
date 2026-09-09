<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

enum Platform: string
{
    case Windows = 'windows';
    case MacOS = 'macos';
    case Linux = 'linux';
    case Android = 'android';
    case iOS = 'ios';
    case ChromeOS = 'chromeos';
}
