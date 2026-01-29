<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Validation;

use JOOservices\UserAgent\Exceptions\InvalidSpecException;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Spec\RandomSpec;

/**
 * Validates GenerationSpec for correctness.
 */
final class SpecValidator
{
    /**
     * Validate spec or throw exception.
     *
     * @throws InvalidSpecException
     */
    public function validate(GenerationSpec $spec): void
    {
        // Version range validation
        if ($spec->versionMin !== null && $spec->versionMax !== null) {
            if ($spec->versionMin > $spec->versionMax) {
                throw InvalidSpecException::invalidVersionRange($spec->versionMin, $spec->versionMax);
            }
        }

        // Exact version conflicts with range
        if ($spec->versionExact !== null && ($spec->versionMin !== null || $spec->versionMax !== null)) {
            throw InvalidSpecException::versionExactConflict();
        }

        // Version values must be positive
        if ($spec->versionMin !== null && $spec->versionMin < 1) {
            throw InvalidSpecException::invalidVersionMin($spec->versionMin);
        }

        if ($spec->versionMax !== null && $spec->versionMax < 1) {
            throw InvalidSpecException::invalidVersionMax($spec->versionMax);
        }

        if ($spec->versionExact !== null && $spec->versionExact < 1) {
            throw InvalidSpecException::invalidVersionExact($spec->versionExact);
        }

        // Version values must not exceed reasonable limits (prevent integer overflow)
        if ($spec->versionMin !== null && $spec->versionMin > 999) {
            throw InvalidSpecException::versionTooHigh('versionMin', $spec->versionMin);
        }

        if ($spec->versionMax !== null && $spec->versionMax > 999) {
            throw InvalidSpecException::versionTooHigh('versionMax', $spec->versionMax);
        }

        if ($spec->versionExact !== null && $spec->versionExact > 999) {
            throw InvalidSpecException::versionTooHigh('versionExact', $spec->versionExact);
        }

        // Channel validation
        $validChannels = ['stable', 'beta', 'dev', 'canary'];
        if ($spec->channel !== null && ! in_array($spec->channel, $validChannels, true)) {
            throw InvalidSpecException::invalidChannel($spec->channel, $validChannels);
        }

        // Arch validation
        $validArch = ['x86_64', 'x64', 'ARM', 'ARM64', 'WOW64', 'i686'];
        if ($spec->arch !== null && ! in_array($spec->arch, $validArch, true)) {
            throw InvalidSpecException::invalidArch($spec->arch, $validArch);
        }

        // Locale validation (basic format check)
        if ($spec->locale !== null && ! $this->isValidLocale($spec->locale)) {
            throw InvalidSpecException::invalidLocale($spec->locale);
        }

        // Tags validation (must be non-empty strings)
        foreach ($spec->tags as $tag) {
            if (! is_string($tag) || trim($tag) === '') {
                throw InvalidSpecException::invalidTag($tag);
            }
        }

        // RandomSpec validation
        if ($spec->randomSpec !== null) {
            $this->validateRandomSpec($spec->randomSpec);
        }
    }

    /**
     * Validate RandomSpec.
     *
     * @throws InvalidSpecException
     */
    private function validateRandomSpec(RandomSpec $randomSpec): void
    {
        if ($randomSpec->historyWindow < 1) {
            throw InvalidSpecException::invalidHistoryWindow($randomSpec->historyWindow);
        }

        if ($randomSpec->historyWindow > 10000) {
            throw InvalidSpecException::historyWindowTooLarge($randomSpec->historyWindow);
        }

        if ($randomSpec->retryBudget < 0) {
            throw InvalidSpecException::invalidRetryBudget($randomSpec->retryBudget);
        }

        if ($randomSpec->retryBudget > 100) {
            throw InvalidSpecException::retryBudgetTooLarge($randomSpec->retryBudget);
        }

        if ($randomSpec->seed !== null && $randomSpec->seed < 0) {
            throw InvalidSpecException::invalidSeed($randomSpec->seed);
        }
    }

    /**
     * Validate locale format (e.g., en-US, fr-FR, zh-CN).
     */
    private function isValidLocale(string $locale): bool
    {
        // Basic validation: 2-5 chars, optional dash, 2-5 chars
        return preg_match('/^[a-z]{2,5}(-[A-Z]{2,5})?$/', $locale) === 1;
    }
}
