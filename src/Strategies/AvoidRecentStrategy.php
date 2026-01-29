<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Strategies;

use JOOservices\UserAgent\History\LruHistory;
use JOOservices\UserAgent\Templates\Browsers\ChromeTemplate;
use JOOservices\UserAgent\Templates\Browsers\EdgeTemplate;
use JOOservices\UserAgent\Templates\Browsers\FirefoxTemplate;
use JOOservices\UserAgent\Templates\Browsers\SafariTemplate;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Selects browser template while avoiding recently used ones.
 *
 * Uses LRU history to prevent repetition.
 */
final class AvoidRecentStrategy
{
    /** @var array<BrowserTemplate> */
    private array $templates;

    private LruHistory $history;

    private int $maxRetries;

    public function __construct(int $historySize = 10, int $maxRetries = 5)
    {
        $this->templates = [
            new ChromeTemplate(),
            new FirefoxTemplate(),
            new SafariTemplate(),
            new EdgeTemplate(),
        ];

        $this->history = new LruHistory($historySize);
        $this->maxRetries = $maxRetries;
    }

    /**
     * Select a browser template, avoiding recently used ones.
     */
    public function select(?int $seed = null): BrowserTemplate
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        $attempts = 0;
        $selected = null;

        while ($attempts < $this->maxRetries) {
            $index = array_rand($this->templates);
            $template = $this->templates[$index];
            $browserName = $template->getBrowser()->value;

            // Check if this browser was recently used
            if (! $this->history->contains($browserName)) {
                $selected = $template;
                break;
            }

            $attempts++;
        }

        // If all retries exhausted, just pick random
        if ($selected === null) {
            $index = array_rand($this->templates);
            $selected = $this->templates[$index];
        }

        // Add to history
        $this->history->add($selected->getBrowser()->value);

        return $selected;
    }

    /**
     * Clear history.
     */
    public function clearHistory(): void
    {
        $this->history->clear();
    }

    /**
     * Get history size.
     */
    public function getHistorySize(): int
    {
        return $this->history->size();
    }
}
