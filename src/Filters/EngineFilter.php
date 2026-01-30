<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Filters;

use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Filter templates by rendering engine.
 */
final class EngineFilter
{
    /**
     * @param array<Engine> $allowedEngines
     */
    public function __construct(
        private readonly array $allowedEngines
    ) {}

    /**
     * Check if template uses allowed engine.
     */
    public function matches(BrowserTemplate $template): bool
    {
        return in_array($template->getEngine(), $this->allowedEngines, true);
    }

    /**
     * Filter array of templates.
     *
     * @param array<BrowserTemplate> $templates
     *
     * @return array<BrowserTemplate>
     */
    public function filter(array $templates): array
    {
        return array_filter(
            $templates,
            fn (BrowserTemplate $template) => $this->matches($template)
        );
    }
}
