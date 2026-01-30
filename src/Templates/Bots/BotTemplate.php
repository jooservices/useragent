<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Templates\Bots;

use JOOservices\UserAgent\Domain\Enums\BotType;

/**
 * Template for generating bot/crawler User-Agent strings.
 */
final class BotTemplate
{
    /**
     * Generate a bot User-Agent string.
     */
    public function generate(BotType $botType, bool $mobile = false): string
    {
        if ($mobile) {
            return $botType->getMobileUserAgent();
        }

        return $botType->getUserAgent();
    }

    /**
     * Generate a random bot User-Agent.
     */
    public function generateRandom(?int $seed = null): string
    {
        $bots = BotType::cases();

        if ($seed !== null) {
            mt_srand($seed);
        }

        $index = mt_rand(0, count($bots) - 1);

        return $bots[$index]->getUserAgent();
    }

    /**
     * Get all available bot types.
     *
     * @return array<BotType>
     */
    public function getAvailableBots(): array
    {
        return BotType::cases();
    }
}
