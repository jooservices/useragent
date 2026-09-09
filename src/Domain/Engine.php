<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

enum Engine: string
{
    case Blink = 'blink';
    case Gecko = 'gecko';
    case WebKit = 'webkit';
}
