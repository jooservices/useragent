<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Spec;

/**
 * Immutable specification for randomization behavior.
 */
final readonly class RandomSpec
{
    public function __construct(
        public ?int $seed = null,
        public int $historyWindow = 100,
        public int $retryBudget = 10,
        public bool $enableHistory = true,
    ) {
    }

    /**
     * Create from array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            seed: $data['seed'] ?? null,
            historyWindow: $data['historyWindow'] ?? 100,
            retryBudget: $data['retryBudget'] ?? 10,
            enableHistory: $data['enableHistory'] ?? true,
        );
    }

    /**
     * Check if seed is set (deterministic mode).
     */
    public function isDeterministic(): bool
    {
        return $this->seed !== null;
    }
}
