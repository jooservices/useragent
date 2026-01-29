<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Exceptions;

/**
 * Exception thrown when spec validation fails.
 */
final class InvalidSpecException extends UserAgentException
{
    public static function invalidFilterSpec(string $reason): self
    {
        return new self(sprintf('Invalid filter spec: %s', $reason));
    }

    public static function invalidRandomSpec(string $reason): self
    {
        return new self(sprintf('Invalid random spec: %s', $reason));
    }

    public static function invalidVersionRange(int $min, int $max): self
    {
        return new self(sprintf('versionMin (%d) cannot be greater than versionMax (%d)', $min, $max));
    }

    public static function versionExactConflict(): self
    {
        return new self('Cannot use versionExact with versionMin/versionMax');
    }

    public static function invalidVersionMin(int $version): self
    {
        return new self(sprintf('versionMin must be >= 1, got %d', $version));
    }

    public static function invalidVersionMax(int $version): self
    {
        return new self(sprintf('versionMax must be >= 1, got %d', $version));
    }

    public static function invalidVersionExact(int $version): self
    {
        return new self(sprintf('versionExact must be >= 1, got %d', $version));
    }

    /**
     * @param array<string> $validChannels
     */
    public static function invalidChannel(string $channel, array $validChannels): self
    {
        return new self(sprintf(
            "Invalid channel '%s'. Must be one of: %s",
            $channel,
            implode(', ', $validChannels)
        ));
    }

    /**
     * @param array<string> $validArch
     */
    public static function invalidArch(string $arch, array $validArch): self
    {
        return new self(sprintf(
            "Invalid arch '%s'. Must be one of: %s",
            $arch,
            implode(', ', $validArch)
        ));
    }

    public static function invalidLocale(string $locale): self
    {
        return new self(sprintf(
            "Invalid locale format '%s'. Expected format: 'en-US', 'fr-FR', etc.",
            $locale
        ));
    }

    public static function invalidTag(mixed $tag): self
    {
        $type = get_debug_type($tag);

        return new self(sprintf('Tag must be non-empty string, got %s', $type));
    }

    public static function invalidHistoryWindow(int $window): self
    {
        return new self(sprintf('historyWindow must be >= 1, got %d', $window));
    }

    public static function historyWindowTooLarge(int $window): self
    {
        return new self(sprintf('historyWindow too large (%d), maximum is 10000', $window));
    }

    public static function invalidRetryBudget(int $budget): self
    {
        return new self(sprintf('retryBudget must be >= 0, got %d', $budget));
    }

    public static function retryBudgetTooLarge(int $budget): self
    {
        return new self(sprintf('retryBudget too large (%d), maximum is 100', $budget));
    }

    public static function versionBelowMinimum(int $version, int $min): self
    {
        return new self(sprintf('versionMin (%d) is below template minimum (%d)', $version, $min));
    }

    public static function versionAboveMaximum(int $version, int $max): self
    {
        return new self(sprintf('versionMax (%d) is above template maximum (%d)', $version, $max));
    }

    public static function versionOutOfRange(int $version, int $min, int $max): self
    {
        return new self(sprintf('versionExact (%d) is outside template range (%d-%d)', $version, $min, $max));
    }

    public static function invalidSeed(int $seed): self
    {
        return new self(sprintf('seed must be >= 0, got %d', $seed));
    }

    public static function versionTooHigh(string $field, int $version): self
    {
        return new self(sprintf('%s too high (%d), maximum is 999', $field, $version));
    }
}
