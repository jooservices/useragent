<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Filters;

use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Composite filter that combines multiple filters with AND/OR logic.
 */
final class CompositeFilter
{
    /**
     * @param array<BrowserFilter|DeviceFilter|OsFilter|EngineFilter|VersionRangeFilter|RiskLevelFilter> $filters
     */
    public function __construct(
        private readonly array $filters,
        private readonly bool $useAndLogic = true
    ) {
    }

    /**
     * Check if template matches composite filter.
     */
    public function matches(BrowserTemplate $template): bool
    {
        if (empty($this->filters)) {
            return true;
        }

        if ($this->useAndLogic) {
            // AND logic: all filters must match
            foreach ($this->filters as $filter) {
                if (! $filter->matches($template)) {
                    return false;
                }
            }

            return true;
        }

        // OR logic: at least one filter must match
        foreach ($this->filters as $filter) {
            if ($filter->matches($template)) {
                return true;
            }
        }

        return false;
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
