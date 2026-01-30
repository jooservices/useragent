<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Strategies;

use JOOservices\UserAgent\Templates\Browsers\ChromeTemplate;
use JOOservices\UserAgent\Templates\Browsers\EdgeTemplate;
use JOOservices\UserAgent\Templates\Browsers\FirefoxTemplate;
use JOOservices\UserAgent\Templates\Browsers\SafariTemplate;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Selects browser template using uniform random distribution.
 *
 * All browsers have equal probability of selection.
 */
final class UniformStrategy
{
    /** @var array<BrowserTemplate> */
    private array $templates;

    public function __construct()
    {
        $this->templates = [
            new ChromeTemplate,
            new FirefoxTemplate,
            new SafariTemplate,
            new EdgeTemplate,
        ];
    }

    /**
     * Select a random browser template with equal probability.
     */
    public function select(?int $seed = null): BrowserTemplate
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        $index = array_rand($this->templates);

        return $this->templates[$index];
    }

    /**
     * Get all available templates.
     *
     * @return array<BrowserTemplate>
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }
}
