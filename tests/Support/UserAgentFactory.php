<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Support;

use Faker\Generator;
use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use JOOservices\UserAgent\Domain\ValueObjects\MarketShare;
use JOOservices\UserAgent\Domain\ValueObjects\Meta;
use JOOservices\UserAgent\Domain\ValueObjects\UserAgent;

/**
 * Factory for generating test UserAgent instances with Faker.
 */
final class UserAgentFactory
{
    public function __construct(
        private readonly Generator $faker,
    ) {
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function make(array $overrides = []): UserAgent
    {
        $uaString = isset($overrides['uaString']) && is_string($overrides['uaString'])
            ? $overrides['uaString']
            : $this->faker->userAgent();

        $meta = isset($overrides['meta']) && $overrides['meta'] instanceof Meta
            ? $overrides['meta']
            : ($this->faker->boolean(70) ? $this->makeRandomMeta() : null);

        return new UserAgent($uaString, $meta);
    }

    /**
     * @return array<UserAgent>
     */
    public function makeMany(int $count): array
    {
        return array_map(fn () => $this->make(), range(1, $count));
    }

    public function makeWithDevice(DeviceType $device): UserAgent
    {
        return new UserAgent(
            uaString: $this->faker->userAgent(),
            meta: $this->makeRandomMeta(['device' => $device]),
        );
    }

    public function makeWithBrowser(BrowserFamily $browser): UserAgent
    {
        return new UserAgent(
            uaString: $this->faker->userAgent(),
            meta: $this->makeRandomMeta(['browser' => $browser]),
        );
    }

    public function makeWithOs(OperatingSystem $os): UserAgent
    {
        return new UserAgent(
            uaString: $this->faker->userAgent(),
            meta: $this->makeRandomMeta(['os' => $os]),
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function makeRandomMeta(array $overrides = []): Meta
    {
        $device = isset($overrides['device']) && $overrides['device'] instanceof DeviceType
            ? $overrides['device']
            : $this->faker->randomElement(DeviceType::cases());

        $os = isset($overrides['os']) && $overrides['os'] instanceof OperatingSystem
            ? $overrides['os']
            : $this->faker->randomElement(OperatingSystem::cases());

        $browser = isset($overrides['browser']) && $overrides['browser'] instanceof BrowserFamily
            ? $overrides['browser']
            : $this->faker->randomElement(BrowserFamily::cases());

        $engine = isset($overrides['engine']) && $overrides['engine'] instanceof Engine
            ? $overrides['engine']
            : $this->faker->randomElement(Engine::cases());

        $version = isset($overrides['version']) && is_int($overrides['version'])
            ? $overrides['version']
            : $this->faker->numberBetween(80, 130);

        $marketShare = isset($overrides['marketShare']) && $overrides['marketShare'] instanceof MarketShare
            ? $overrides['marketShare']
            : new MarketShare($this->faker->randomFloat(2, 0, 100));

        $riskLevel = isset($overrides['riskLevel']) && $overrides['riskLevel'] instanceof RiskLevel
            ? $overrides['riskLevel']
            : $this->faker->randomElement(RiskLevel::cases());

        $tags = isset($overrides['tags']) && is_array($overrides['tags'])
            ? array_values(array_filter($overrides['tags'], 'is_string'))
            : $this->faker->words($this->faker->numberBetween(0, 5));

        /**
         * @phpstan-ignore argument.type
         */
        return new Meta(
            device: $device,
            os: $os,
            browser: $browser,
            engine: $engine,
            version: $version,
            marketShare: $marketShare,
            riskLevel: $riskLevel,
            tags: $tags,
        );
    }
}
