<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Dataset;

final class Schema
{
    public const int VERSION = 1;
    public const array PAYLOAD_FILES = [
        'compatibility.json',
        'browsers.json',
        'platforms.json',
        'models.json',
        'templates.json',
    ];
    public const array PLACEHOLDERS = [
        'osToken', 'osVersion', 'archToken', 'fullVersion', 'model', 'deviceToken', 'locale',
    ];

    private function __construct()
    {
    }
}
