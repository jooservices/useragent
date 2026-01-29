<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Templates\Catalogs;

/**
 * Catalog of mobile device models.
 */
final class ModelCatalog
{
    /**
     * Get popular Android models with weights.
     *
     * @return array<string, float>
     */
    public static function getAndroidModels(): array
    {
        return [
            'SM-G998B' => 0.15,      // Samsung Galaxy S21 Ultra
            'SM-A525F' => 0.12,      // Samsung Galaxy A52
            'Pixel 7 Pro' => 0.10,   // Google Pixel 7 Pro
            'SM-S918B' => 0.09,      // Samsung Galaxy S23 Ultra
            'Pixel 8' => 0.08,       // Google Pixel 8
            'SM-A546B' => 0.07,      // Samsung Galaxy A54
            'OnePlus 11' => 0.06,    // OnePlus 11
            'Xiaomi 13' => 0.06,     // Xiaomi 13
            'SM-M336B' => 0.05,      // Samsung Galaxy M33
            'Redmi Note 12' => 0.05, // Redmi Note 12
            'POCO X5 Pro' => 0.04,   // POCO X5 Pro
            'Moto G Power' => 0.03,  // Motorola Moto G Power
            'Nokia G50' => 0.03,     // Nokia G50
            'Oppo Find X5' => 0.03,  // Oppo Find X5
            'Vivo V27' => 0.02,      // Vivo V27
            'Realme GT 2' => 0.02,   // Realme GT 2
        ];
    }

    /**
     * Get popular iOS models with weights.
     *
     * @return array<string, float>
     */
    public static function getIosModels(): array
    {
        return [
            'iPhone15,3' => 0.20,  // iPhone 14 Pro Max
            'iPhone15,2' => 0.18,  // iPhone 14 Pro
            'iPhone14,3' => 0.15,  // iPhone 13 Pro Max
            'iPhone14,2' => 0.12,  // iPhone 13 Pro
            'iPhone14,5' => 0.10,  // iPhone 13
            'iPhone13,4' => 0.08,  // iPhone 12 Pro Max
            'iPhone13,3' => 0.07,  // iPhone 12 Pro
            'iPhone13,2' => 0.05,  // iPhone 12
            'iPhone12,1' => 0.03,  // iPhone 11
            'iPhone11,8' => 0.02,  // iPhone XR
        ];
    }

    /**
     * Get a random Android model.
     */
    public static function getRandomAndroidModel(?int $seed = null): string
    {
        return self::getRandomFromWeighted(self::getAndroidModels(), $seed);
    }

    /**
     * Get a random iOS model.
     */
    public static function getRandomIosModel(?int $seed = null): string
    {
        return self::getRandomFromWeighted(self::getIosModels(), $seed);
    }

    /**
     * Get random item from weighted array.
     *
     * @param array<string, float> $items
     */
    private static function getRandomFromWeighted(array $items, ?int $seed = null): string
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0.0;

        foreach ($items as $item => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $item;
            }
        }

        return array_key_first($items) ?? 'SM-G998B'; // Fallback (should never happen)
    }
}
