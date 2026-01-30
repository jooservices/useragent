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
    ) {}

    /**
     * @param array<string, mixed> $overrides
     */
    public function make(array $overrides = []): UserAgent
    {
        $uaString = $this->resolveValue($overrides, 'uaString', 'string', fn () => $this->faker->userAgent());

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
        /** @var DeviceType $device */
        $device = $this->resolveValue($overrides, 'device', DeviceType::class, fn () => $this->faker->randomElement(DeviceType::cases()));
        /** @var OperatingSystem $os */
        $os = $this->resolveValue($overrides, 'os', OperatingSystem::class, fn () => $this->faker->randomElement(OperatingSystem::cases()));
        /** @var BrowserFamily $browser */
        $browser = $this->resolveValue($overrides, 'browser', BrowserFamily::class, fn () => $this->faker->randomElement(BrowserFamily::cases()));
        /** @var Engine $engine */
        $engine = $this->resolveValue($overrides, 'engine', Engine::class, fn () => $this->faker->randomElement(Engine::cases()));
        /** @var int $version */
        $version = $this->resolveValue($overrides, 'version', 'integer', fn () => $this->faker->numberBetween(80, 130));
        /** @var MarketShare $marketShare */
        $marketShare = $this->resolveValue($overrides, 'marketShare', MarketShare::class, fn () => new MarketShare($this->faker->randomFloat(2, 0, 100)));
        /** @var RiskLevel $riskLevel */
        $riskLevel = $this->resolveValue($overrides, 'riskLevel', RiskLevel::class, fn () => $this->faker->randomElement(RiskLevel::cases()));
        /** @var array<string> $tags */
        $tags = $this->resolveValue($overrides, 'tags', 'array', fn () => $this->faker->words($this->faker->numberBetween(0, 5)));

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

    /**
     * @template T of mixed
     *
     * @param array<string, mixed> $overrides
     * @param string|class-string  $type
     * @param callable(): T        $default
     */
    private function resolveValue(array $overrides, string $key, string $type, callable $default): mixed
    {
        if (! isset($overrides[$key])) {
            return $default();
        }

        $value = $overrides[$key];

        if (class_exists($type) || interface_exists($type)) {
            if ($value instanceof $type) {
                return $value;
            }
        } elseif (gettype($value) === $type) {
            return $value;
        }

        return $default();
    }
}
