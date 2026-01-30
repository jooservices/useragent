<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Service;

use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Pickers\ArchPicker;
use JOOservices\UserAgent\Pickers\LocalePicker;
use JOOservices\UserAgent\Pickers\ModelPicker;
use JOOservices\UserAgent\Pickers\VersionPicker;
use JOOservices\UserAgent\Renderers\UserAgentRenderer;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Strategies\WeightedStrategy;
use JOOservices\UserAgent\Templates\BrowserTemplate;
use JOOservices\UserAgent\Validation\SpecValidator;

/**
 * Main service for generating user-agent strings.
 *
 * Orchestrates all components to produce realistic UA strings.
 */
final class UserAgentService
{
    private VersionPicker $versionPicker;

    private ModelPicker $modelPicker;

    private LocalePicker $localePicker;

    private ArchPicker $archPicker;

    private UserAgentRenderer $renderer;

    private SpecValidator $validator;

    private WeightedStrategy $strategy;

    public function __construct()
    {
        $this->versionPicker = new VersionPicker;
        $this->modelPicker = new ModelPicker;
        $this->localePicker = new LocalePicker;
        $this->archPicker = new ArchPicker;
        $this->renderer = new UserAgentRenderer;
        $this->validator = new SpecValidator;
        $this->strategy = new WeightedStrategy;
    }

    /**
     * Generate a user-agent string.
     */
    public function generate(?GenerationSpec $spec = null, ?int $seed = null): string
    {
        $spec = $spec ?? new GenerationSpec;

        // Validate spec constraints
        $this->validator->validate($spec);

        // Select browser template (use weighted strategy by default)
        $template = $this->selectTemplate($spec, $seed);

        // Determine device type and OS
        $device = $spec->device ?? DeviceType::Desktop;
        $os = $this->selectOs($template, $device, $spec);

        // Pick version
        $version = $this->versionPicker->pick($template, $spec, $seed);

        // Build context for rendering
        $context = $this->buildContext($template, $device, $os, $version, $spec, $seed);

        // Render final UA string
        return $this->renderer->render($template, $device, $os, $context);
    }

    /**
     * Select browser template.
     *
     * @throws \JOOservices\UserAgent\Exceptions\NoCandidateException
     */
    private function selectTemplate(GenerationSpec $spec, ?int $seed): BrowserTemplate
    {
        // 1. Get all available templates
        $candidates = [
            new \JOOservices\UserAgent\Templates\Browsers\ChromeTemplate,
            new \JOOservices\UserAgent\Templates\Browsers\FirefoxTemplate,
            new \JOOservices\UserAgent\Templates\Browsers\SafariTemplate,
            new \JOOservices\UserAgent\Templates\Browsers\EdgeTemplate,
        ];

        // 2. Build filters based on spec
        $filters = [];

        if ($spec->browser !== null) {
            $filters[] = new \JOOservices\UserAgent\Filters\BrowserFilter([$spec->browser]);
        }

        if ($spec->device !== null) {
            $filters[] = new \JOOservices\UserAgent\Filters\DeviceFilter([$spec->device]);
        }

        if ($spec->os !== null) {
            $filters[] = new \JOOservices\UserAgent\Filters\OsFilter([$spec->os], $spec->device);
        }

        if ($spec->versionMin !== null || $spec->versionMax !== null) {
            $filters[] = new \JOOservices\UserAgent\Filters\VersionRangeFilter($spec->versionMin, $spec->versionMax);
        }

        // 3. Apply filters
        if (! empty($filters)) {
            $composite = new \JOOservices\UserAgent\Filters\CompositeFilter($filters);
            $candidates = $composite->filter($candidates);
        }

        if (empty($candidates)) {
            throw new \JOOservices\UserAgent\Exceptions\NoCandidateException('No browser templates match the provided specification.');
        }

        // 4. Select using strategy
        $strategyClass = $spec->strategy ?? WeightedStrategy::class;

        // Handling for existing instance of weighted strategy or new instantiation
        if ($strategyClass === WeightedStrategy::class) {
            // Re-instantiate with filtered candidates if needed, but WeightedStrategy currently
            // hardcodes its templates. We need to respect the filtered list.
            // For now, let's just pick randomly from candidates using weights if provided.
            // But WeightedStrategy doesn't accept candidates in select().
            // This suggests a design flaw where Strategy should accept candidates.
            // To fix quickly: Pick one from filtered candidates based on their internal weights.

            return $this->selectWeighted($candidates, $seed);
        }

        // For other strategies (Uniform, etc.) - simple random for now if not implemented fully
        // Assuming Uniform for fallback
        if ($seed !== null) {
            mt_srand($seed);
        }

        return $candidates[array_rand($candidates)];
    }

    /**
     * Helper to select weighted from specific candidates.
     *
     * @param array<BrowserTemplate> $candidates
     */
    private function selectWeighted(array $candidates, ?int $seed): BrowserTemplate
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        $weights = array_map(fn ($t) => $t->getMarketShare()->percentage, $candidates);
        $total = array_sum($weights);

        $random = (mt_rand() / mt_getrandmax()) * $total;
        $current = 0.0;

        foreach ($candidates as $i => $candidate) {
            $current += $weights[$i];
            if ($random <= $current) {
                return $candidate;
            }
        }

        return $candidates[0];
    }

    /**
     * Select operating system.
     */
    private function selectOs(
        BrowserTemplate $template,
        DeviceType $device,
        GenerationSpec $spec
    ): OperatingSystem {
        // Use spec OS if provided
        if ($spec->os !== null) {
            return $spec->os;
        }

        // Get supported OS list for this device
        $supportedOs = $template->getSupportedOs($device);

        if (empty($supportedOs)) {
            return OperatingSystem::Windows; // Fallback
        }

        // Pick first supported OS (could be randomized)
        return $supportedOs[0];
    }

    /**
     * Build rendering context.
     *
     * @return array<string, mixed>
     */
    private function buildContext(
        BrowserTemplate $_template,
        DeviceType $device,
        OperatingSystem $os,
        int $version,
        GenerationSpec $spec,
        ?int $seed
    ): array {
        $context = [
            'version' => $version,
            'browserVersion' => $version,
            'locale' => $this->localePicker->pick($spec, $seed),
            'arch' => $this->archPicker->pick($device, $spec, $seed),
        ];

        // Add mobile-specific context
        if ($device === DeviceType::Mobile || $device === DeviceType::Tablet) {
            $context['model'] = $this->modelPicker->pick($os, $spec, $seed);
        }

        // Add OS version (simplified - could be more sophisticated)
        $context['os_version'] = $this->getOsVersion($os);

        return $context;
    }

    /**
     * Get OS version string.
     */
    private function getOsVersion(OperatingSystem $os): string
    {
        return match ($os) {
            OperatingSystem::Windows => mt_rand(0, 1) ? '10.0' : '11.0',
            OperatingSystem::MacOS => '14.'.mt_rand(0, 5),
            OperatingSystem::Linux => '5.'.mt_rand(10, 19),
            OperatingSystem::Android => (string) mt_rand(10, 14),
            OperatingSystem::iOS => '17.'.mt_rand(0, 4),
            OperatingSystem::ChromeOS => (string) mt_rand(120, 145),
        };
    }
}
