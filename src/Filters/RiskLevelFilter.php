<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Filters;

use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Filter templates by risk level.
 */
final class RiskLevelFilter
{
    /**
     * @param array<RiskLevel> $allowedLevels
     */
    public function __construct(
        private readonly array $allowedLevels
    ) {}

    /**
     * Check if template's risk level is allowed.
     */
    public function matches(BrowserTemplate $template): bool
    {
        return in_array($template->getRiskLevel(), $this->allowedLevels, true);
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
