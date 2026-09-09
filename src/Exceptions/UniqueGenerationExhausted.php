<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Exceptions;

final class UniqueGenerationExhausted extends UserAgentException
{
    private function __construct(
        public readonly int $requested,
        public readonly int $produced,
        public readonly int $attempts,
    ) {
        parent::__construct(sprintf(
            'Unique generation exhausted: requested %d, produced %d after %d attempts.',
            $requested,
            $produced,
            $attempts,
        ));
    }

    public static function forBatch(int $requested, int $produced, int $attempts): self
    {
        return new self($requested, $produced, $attempts);
    }

    /** @return array{error: string, message: string, requested: int, produced: int, attempts: int} */
    #[\Override]
    public function toArray(): array
    {
        return parent::toArray() + [
            'requested' => $this->requested,
            'produced' => $this->produced,
            'attempts' => $this->attempts,
        ];
    }
}
