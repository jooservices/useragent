<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Templates\Catalogs;

/**
 * Catalog of locale codes with weights.
 */
final class LocaleCatalog
{
    /**
     * Get all supported locales with their weights.
     *
     * @return array<string, float>
     */
    public static function getLocales(): array
    {
        return [
            'en-US' => 0.54,
            'zh-CN' => 0.19,
            'es-ES' => 0.06,
            'en-GB' => 0.05,
            'fr-FR' => 0.03,
            'de-DE' => 0.03,
            'ja-JP' => 0.02,
            'pt-BR' => 0.02,
            'ru-RU' => 0.02,
            'ko-KR' => 0.01,
            'it-IT' => 0.01,
            'ar-SA' => 0.01,
            'hi-IN' => 0.01,
        ];
    }

    /**
     * Get a random locale based on weights.
     */
    public static function getRandomLocale(?int $seed = null): string
    {
        $locales = self::getLocales();

        if ($seed !== null) {
            mt_srand($seed);
        }

        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0.0;

        foreach ($locales as $locale => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $locale;
            }
        }

        return 'en-US'; // Fallback
    }

    /**
     * Check if locale is supported.
     */
    public static function isSupported(string $locale): bool
    {
        return isset(self::getLocales()[$locale]);
    }
}
