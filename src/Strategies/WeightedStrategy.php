<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Strategies;

use JOOservices\UserAgent\Templates\Browsers\ChromeTemplate;
use JOOservices\UserAgent\Templates\Browsers\EdgeTemplate;
use JOOservices\UserAgent\Templates\Browsers\FirefoxTemplate;
use JOOservices\UserAgent\Templates\Browsers\SafariTemplate;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Selects browser template based on market share weights.
 *
 * Browsers with higher market share are more likely to be selected.
 */
final class WeightedStrategy
{
    /** @var array<BrowserTemplate> */
    private array $templates;

    /** @var array<float> */
    private array $weights;

    private float $totalWeight;

    public function __construct()
    {
        $this->templates = [
            new ChromeTemplate,
            new FirefoxTemplate,
            new SafariTemplate,
            new EdgeTemplate,
        ];

        // Extract market share weights
        $this->weights = array_map(
            fn (BrowserTemplate $template) => $template->getMarketShare()->percentage,
            $this->templates
        );

        $this->totalWeight = array_sum($this->weights);
    }

    /**
     * Select a browser template based on market share weights.
     */
    public function select(?int $seed = null): BrowserTemplate
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        // Generate random value between 0 and total weight
        $random = (mt_rand() / mt_getrandmax()) * $this->totalWeight;
        $cumulative = 0.0;

        foreach ($this->templates as $index => $template) {
            $cumulative += $this->weights[$index];
            if ($random <= $cumulative) {
                return $template;
            }
        }

        // Fallback to most popular (Chrome)
        return $this->templates[0];
    }

    /**
     * Get all templates with their weights.
     *
     * @return array<array{template: BrowserTemplate, weight: float}>
     */
    public function getTemplatesWithWeights(): array
    {
        $result = [];
        foreach ($this->templates as $index => $template) {
            $result[] = [
                'template' => $template,
                'weight' => $this->weights[$index],
            ];
        }

        return $result;
    }

    /**
     * Get total weight.
     */
    public function getTotalWeight(): float
    {
        return $this->totalWeight;
    }
}
