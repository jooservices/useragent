<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

enum BrowserFamily: string
{
    case Chrome = 'chrome';
    case Firefox = 'firefox';
    case Safari = 'safari';
    case Edge = 'edge';
}
