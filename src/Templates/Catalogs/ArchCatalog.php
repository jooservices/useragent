<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Templates\Catalogs;

/**
 * Catalog of CPU architectures with weights.
 */
final class ArchCatalog
{
    /**
     * Get all supported architectures with their weights.
     *
     * @return array<string, float>
     */
    public static function getArchitectures(): array
    {
        return [
            'x86_64' => 0.45,  // 64-bit x86 (Intel/AMD)
            'x64' => 0.30,     // Windows x64
            'ARM64' => 0.15,   // ARM 64-bit (Apple Silicon, mobile)
            'ARM' => 0.05,     // ARM 32-bit (older mobile)
            'WOW64' => 0.03,   // Windows on Windows 64-bit
            'i686' => 0.02,    // 32-bit x86 (legacy)
        ];
    }

    /**
     * Get architectures for desktop platforms.
     *
     * @return array<string, float>
     */
    public static function getDesktopArchitectures(): array
    {
        return [
            'x86_64' => 0.60,
            'x64' => 0.25,
            'ARM64' => 0.12,  // Apple Silicon
            'WOW64' => 0.02,
            'i686' => 0.01,
        ];
    }

    /**
     * Get architectures for mobile platforms.
     *
     * @return array<string, float>
     */
    public static function getMobileArchitectures(): array
    {
        return [
            'ARM64' => 0.85,
            'ARM' => 0.15,
        ];
    }

    /**
     * Get a random architecture based on weights.
     */
    public static function getRandomArchitecture(?int $seed = null, bool $isMobile = false): string
    {
        $architectures = $isMobile ? self::getMobileArchitectures() : self::getDesktopArchitectures();

        if ($seed !== null) {
            mt_srand($seed);
        }

        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0.0;

        foreach ($architectures as $arch => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $arch;
            }
        }

        return $isMobile ? 'ARM64' : 'x86_64'; // Fallback
    }

    /**
     * Check if architecture is supported.
     */
    public static function isSupported(string $arch): bool
    {
        return isset(self::getArchitectures()[$arch]);
    }
}
