<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Console;

use JOOservices\UserAgent\Exceptions\ConsoleException;

final class ArgvParser
{
    private const array VALUE_FLAGS = [
        'browser', 'device', 'os', 'locale', 'arch', 'version', 'version-min',
        'version-max', 'recent', 'selection', 'seed', 'count', 'format',
    ];
    private const array BOOLEAN_FLAGS = ['unique', 'help'];

    /**
     * @param list<string> $argv
     * @return array<string, string|bool>
     */
    public function parse(array $argv): array
    {
        $parsed = [];
        foreach (array_slice($argv, 1) as $argument) {
            if (!str_starts_with($argument, '--')) {
                throw new ConsoleException("Unexpected positional argument: {$argument}");
            }
            $raw = substr($argument, 2);
            if (in_array($raw, self::BOOLEAN_FLAGS, true)) {
                $parsed[$raw] = true;
                continue;
            }
            $parts = explode('=', $raw, 2);
            if (count($parts) !== 2 || !in_array($parts[0], self::VALUE_FLAGS, true) || $parts[1] === '') {
                throw new ConsoleException("Unknown or invalid option: {$argument}");
            }
            if (array_key_exists($parts[0], $parsed)) {
                throw new ConsoleException("Option --{$parts[0]} may only be provided once.");
            }
            $parsed[$parts[0]] = $parts[1];
        }

        return $parsed;
    }
}
