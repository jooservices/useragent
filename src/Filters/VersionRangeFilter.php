<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Filters;

use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Filter templates by version range.
 */
final class VersionRangeFilter
{
    public function __construct(
        private readonly ?int $minVersion = null,
        private readonly ?int $maxVersion = null
    ) {}

    /**
     * Check if template's version range overlaps with filter range.
     */
    public function matches(BrowserTemplate $template): bool
    {
        $templateMin = $template->getMinVersion();
        $templateMax = $template->getMaxVersion();

        // If no constraints, match all
        if ($this->minVersion === null && $this->maxVersion === null) {
            return true;
        }

        // Check if ranges overlap
        if ($this->minVersion !== null && $templateMax < $this->minVersion) {
            return false;
        }

        if ($this->maxVersion !== null && $templateMin > $this->maxVersion) {
            return false;
        }

        return true;
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
