<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Renderers;

use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Renders user-agent strings from browser templates.
 *
 * Fills template placeholders with actual values.
 */
final class UserAgentRenderer
{
    /**
     * Render a user-agent string.
     *
     * @param array<string, mixed> $context
     */
    public function render(
        BrowserTemplate $template,
        DeviceType $device,
        OperatingSystem $os,
        array $context
    ): string {
        // Get appropriate template string
        $templateString = $this->getTemplateString($template, $device, $os);

        // Replace placeholders with actual values
        return $this->replacePlaceholders($templateString, $context);
    }

    /**
     * Get template string for device/OS combination.
     */
    private function getTemplateString(
        BrowserTemplate $template,
        DeviceType $device,
        OperatingSystem $os
    ): string {
        return match ($device) {
            DeviceType::Desktop => $template->getDesktopTemplate($os),
            DeviceType::Mobile, DeviceType::Tablet => $template->getMobileTemplate($os),
            DeviceType::Bot => '', // Bots not yet implemented
        };
    }

    /**
     * Replace placeholders in template string.
     *
     * @param array<string, mixed> $context
     */
    private function replacePlaceholders(string $template, array $context): string
    {
        $result = $template;

        foreach ($context as $key => $value) {
            $placeholder = '{' . $key . '}';
            $result = str_replace($placeholder, (string) $value, $result);
        }

        return $result;
    }

    /**
     * Get all placeholders from a template string.
     *
     * @return array<string>
     */
    public function extractPlaceholders(string $template): array
    {
        preg_match_all('/\{([^}]+)\}/', $template, $matches);

        return $matches[1] ?? [];
    }
}
