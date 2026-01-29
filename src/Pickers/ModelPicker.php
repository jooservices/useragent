<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Pickers;

use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Templates\Catalogs\ModelCatalog;

/**
 * Picks mobile device model based on OS and weights.
 */
final class ModelPicker
{
    /**
     * Pick model for given OS.
     */
    public function pick(OperatingSystem $os, GenerationSpec $spec, ?int $seed = null): string
    {
        return match ($os) {
            OperatingSystem::Android => ModelCatalog::getRandomAndroidModel($seed),
            OperatingSystem::iOS => ModelCatalog::getRandomIosModel($seed),
            default => 'Unknown',
        };
    }
}
