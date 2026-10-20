<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Exceptions;

final class IncompatibleRequestException extends UserAgentException
{
    /**
     * @param array<string, int|string|null> $requested
     * @param list<array{browser: string, device: string, platform: string}> $alternatives
     * @param array<string, list<int>> $availableVersions
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly array $requested = [],
        public readonly array $alternatives = [],
        public readonly array $availableVersions = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param array{browser: ?string, device: ?string, platform: ?string} $requested
     * @param list<array{browser: string, device: string, platform: string}> $alternatives
     */
    public static function forTuple(array $requested, string $reason, array $alternatives): self
    {
        return self::forRequest($requested, $reason, $alternatives);
    }

    /**
     * @param array<string, int|string|null> $requested
     * @param list<array{browser: string, device: string, platform: string}> $alternatives
     * @param array<string, list<int>> $availableVersions
     */
    public static function forRequest(
        array $requested,
        string $reason,
        array $alternatives,
        array $availableVersions = [],
    ): self {
        $message = $reason . ' Requested: ' . json_encode($requested, JSON_THROW_ON_ERROR);
        if ($availableVersions !== []) {
            $message .= ' Available versions: ' . json_encode($availableVersions, JSON_THROW_ON_ERROR);
        }
        if ($alternatives !== []) {
            $message .= ' Alternatives: ' . json_encode($alternatives, JSON_THROW_ON_ERROR);
        }

        return new self(
            message: $message,
            requested: $requested,
            alternatives: $alternatives,
            availableVersions: $availableVersions,
        );
    }

    /**
     * @return array{error: string, message: string, requested: array<string, int|string|null>, alternatives: list<array{browser: string, device: string, platform: string}>, availableVersions: array<string, list<int>>}
     */
    #[\Override]
    public function toArray(): array
    {
        return parent::toArray() + [
            'requested' => $this->requested,
            'alternatives' => $this->alternatives,
            'availableVersions' => $this->availableVersions,
        ];
    }
}
