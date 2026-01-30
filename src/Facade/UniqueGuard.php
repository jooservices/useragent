<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Facade;

final class UniqueGuard
{
    /** @var array<string, bool> */
    private static array $history = [];

    public static function check(string $ua): bool
    {
        return ! isset(self::$history[$ua]);
    }

    public static function add(string $ua): void
    {
        self::$history[$ua] = true;
    }

    public static function reset(): void
    {
        self::$history = [];
    }
}
