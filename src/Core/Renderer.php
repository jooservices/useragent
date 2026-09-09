<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Domain\UserAgentProfile;
use JOOservices\UserAgent\Exceptions\RenderException;

final class Renderer
{
    public function __construct(private readonly UserAgentStringValidator $validator = new UserAgentStringValidator())
    {
    }

    public function render(string $template, UserAgentProfile $profile): string
    {
        $values = [
            '{osToken}' => $profile->osToken,
            '{osVersion}' => $profile->osVersion,
            '{archToken}' => $profile->archToken,
            '{fullVersion}' => $profile->browserVersion,
            '{model}' => $profile->model,
            '{deviceToken}' => $profile->deviceToken,
            '{locale}' => $profile->locale,
        ];
        foreach ($values as $placeholder => $value) {
            if (str_contains($template, $placeholder) && ($value === null || $value === '')) {
                throw new RenderException("Missing value for {$placeholder}.");
            }
        }
        /** @var array<string, string> $replacements */
        $replacements = array_filter($values, static fn(?string $value): bool => $value !== null);
        $rendered = strtr($template, $replacements);
        if (preg_match('/\{[^}]+\}/', $rendered) === 1) {
            throw new RenderException('Rendered User-Agent contains an unresolved placeholder.');
        }
        if (str_contains($template, '{archToken}') && !str_contains($rendered, $profile->archToken)) {
            throw new RenderException('Rendered User-Agent does not contain its architecture token.');
        }
        $this->validator->assertValid($rendered);
        if (!$this->profileMatches($rendered, $profile)) {
            throw new RenderException('Rendered User-Agent does not match its profile.');
        }

        return $rendered;
    }

    public function profileMatches(string $userAgent, UserAgentProfile $profile): bool
    {
        if (!str_contains($userAgent, $profile->browserVersion) || !str_contains($userAgent, $profile->osToken)) {
            return false;
        }
        if ($profile->device === DeviceClass::Tablet && $profile->platform === Platform::iOS) {
            return str_contains($userAgent, 'iPad') && !str_contains($userAgent, 'iPhone');
        }
        if ($profile->device === DeviceClass::Mobile && $profile->platform === Platform::iOS) {
            return str_contains($userAgent, 'iPhone');
        }

        return true;
    }
}
