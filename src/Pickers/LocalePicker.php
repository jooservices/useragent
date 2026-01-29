<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Pickers;

use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Templates\Catalogs\LocaleCatalog;

/**
 * Picks locale based on weights.
 */
final class LocalePicker
{
    /**
     * Pick locale.
     */
    public function pick(GenerationSpec $spec, ?int $seed = null): string
    {
        // Use spec locale if provided
        if ($spec->locale !== null) {
            return $spec->locale;
        }

        // Otherwise use weighted random from catalog
        return LocaleCatalog::getRandomLocale($seed);
    }
}
