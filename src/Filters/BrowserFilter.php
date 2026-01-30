<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Filters;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Filter templates by browser family.
 */
final class BrowserFilter
{
    /**
     * @param array<BrowserFamily> $allowedBrowsers
     */
    public function __construct(
        private readonly array $allowedBrowsers
    ) {}

    /**
     * Check if template matches filter.
     */
    public function matches(BrowserTemplate $template): bool
    {
        return in_array($template->getBrowser(), $this->allowedBrowsers, true);
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
