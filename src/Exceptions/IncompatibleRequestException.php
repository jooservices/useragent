<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Exceptions;

final class IncompatibleRequestException extends UserAgentException
{
    /**
     * @param array{browser: ?string, device: ?string, platform: ?string} $requested
     * @param list<array{browser: string, device: string, platform: string}> $alternatives
     */
    public static function forTuple(array $requested, string $reason, array $alternatives): self
    {
        $suffix = $alternatives === [] ? '' : ' Alternatives: ' . json_encode($alternatives, JSON_THROW_ON_ERROR);

        return new self($reason . ' Requested: ' . json_encode($requested, JSON_THROW_ON_ERROR) . $suffix);
    }
}
