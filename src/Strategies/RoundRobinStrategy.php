<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Strategies;

use JOOservices\UserAgent\Templates\Browsers\ChromeTemplate;
use JOOservices\UserAgent\Templates\Browsers\EdgeTemplate;
use JOOservices\UserAgent\Templates\Browsers\FirefoxTemplate;
use JOOservices\UserAgent\Templates\Browsers\SafariTemplate;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Selects browser templates in round-robin fashion.
 *
 * Cycles through browsers sequentially for even distribution.
 */
final class RoundRobinStrategy
{
    /** @var array<BrowserTemplate> */
    private array $templates;

    private int $currentIndex = 0;

    public function __construct()
    {
        $this->templates = [
            new ChromeTemplate(),
            new FirefoxTemplate(),
            new SafariTemplate(),
            new EdgeTemplate(),
        ];
    }

    /**
     * Select next browser template in round-robin order.
     */
    public function select(): BrowserTemplate
    {
        $template = $this->templates[$this->currentIndex];

        // Move to next index, wrap around if at end
        $this->currentIndex = ($this->currentIndex + 1) % count($this->templates);

        return $template;
    }

    /**
     * Reset to first template.
     */
    public function reset(): void
    {
        $this->currentIndex = 0;
    }

    /**
     * Get current index.
     */
    public function getCurrentIndex(): int
    {
        return $this->currentIndex;
    }

    /**
     * Get total template count.
     */
    public function getCount(): int
    {
        return count($this->templates);
    }
}
