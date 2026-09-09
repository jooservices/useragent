<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Console;

use JOOservices\UserAgent\Domain\BatchResult;
use JOOservices\UserAgent\Domain\GenerationResult;
use LogicException;

final class OutputFormatter
{
    public function format(BatchResult $result, string $format): string
    {
        return match ($format) {
            'text' => implode("\n", array_map(static fn(GenerationResult $entry): string => $entry->userAgent, $result->entries)) . "\n",
            'json' => json_encode(
                array_map(static fn(GenerationResult $entry): array => $entry->toArray(), $result->entries),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
            ) . "\n",
            'ndjson' => implode("\n", array_map(
                static fn(GenerationResult $entry): string => $entry->toJson(flags: JSON_UNESCAPED_SLASHES),
                $result->entries,
            )) . "\n",
            default => throw new LogicException('Unsupported output format.'),
        };
    }
}
