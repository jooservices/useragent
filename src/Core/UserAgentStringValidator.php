<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use JOOservices\UserAgent\Exceptions\RenderException;

final class UserAgentStringValidator
{
    public function assertValid(string $userAgent): void
    {
        if ($userAgent === '' || strlen($userAgent) > 512) {
            throw new RenderException('Rendered User-Agent violates output length limits.');
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $userAgent) === 1) {
            throw new RenderException('Rendered User-Agent contains control characters.');
        }
    }
}
