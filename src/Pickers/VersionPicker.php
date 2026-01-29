<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Pickers;

use JOOservices\UserAgent\Exceptions\InvalidSpecException;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Picks browser version based on spec constraints.
 *
 * Strategies:
 * - Exact: Use versionExact if specified
 * - Range: Random within versionMin...versionMax
 * - Latest: Use stable version if no constraints
 * - Weighted recency: Prefer recent versions (optional)
 */
final class VersionPicker
{
    /**
     * Pick version for given template and spec.
     *
     * @throws InvalidSpecException
     */
    public function pick(BrowserTemplate $template, GenerationSpec $spec, ?int $seed = null): int
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        // Exact version specified
        if ($spec->versionExact !== null) {
            $this->validateVersion($spec->versionExact, $template);

            return $spec->versionExact;
        }

        // Range specified
        if ($spec->versionMin !== null || $spec->versionMax !== null) {
            return $this->pickInRange(
                $spec->versionMin ?? $template->getMinVersion(),
                $spec->versionMax ?? $template->getMaxVersion(),
                $template
            );
        }

        // Default: stable version
        return $template->getStableVersion();
    }

    /**
     * Pick version within range.
     *
     * @throws InvalidSpecException
     */
    private function pickInRange(int $min, int $max, BrowserTemplate $template): int
    {
        // Validate range against template
        $templateMin = $template->getMinVersion();
        $templateMax = $template->getMaxVersion();

        if ($min < $templateMin) {
            throw InvalidSpecException::versionBelowMinimum($min, $templateMin);
        }

        if ($max > $templateMax) {
            throw InvalidSpecException::versionAboveMaximum($max, $templateMax);
        }

        // Random within range
        return mt_rand($min, $max);
    }

    /**
     * Validate version against template bounds.
     *
     * @throws InvalidSpecException
     */
    private function validateVersion(int $version, BrowserTemplate $template): void
    {
        $min = $template->getMinVersion();
        $max = $template->getMaxVersion();

        if ($version < $min || $version > $max) {
            throw InvalidSpecException::versionOutOfRange($version, $min, $max);
        }
    }

    /**
     * Pick with weighted recency (prefer recent versions).
     *
     * Weight calculation: newer versions get higher probability
     */
    public function pickWeightedRecent(int $min, int $max, ?int $seed = null): int
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        $versions = range($min, $max);
        $count = count($versions);

        // Linear weight: oldest = 1, newest = count
        $weights = range(1, $count);
        $totalWeight = array_sum($weights);

        // Weighted random selection
        $random = mt_rand(1, (int) $totalWeight);
        $cumulative = 0;

        foreach ($versions as $index => $version) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $version;
            }
        }

        return $max; // Fallback
    }
}
