<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\ValueObjects;

use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\ValueObjects\Meta;
use JOOservices\UserAgent\Domain\ValueObjects\UserAgent;
use JOOservices\UserAgent\Tests\Support\UserAgentFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \JOOservices\UserAgent\Domain\ValueObjects\UserAgent
 */
final class UserAgentTest extends TestCase
{
    private Generator $faker;

    private UserAgentFactory $factory;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->factory = new UserAgentFactory($this->faker);
    }

    public function test_creates_with_ua_string_only(): void
    {
        $uaString = $this->faker->userAgent();
        $ua = new UserAgent($uaString);

        $this->assertSame($uaString, $ua->uaString);
        $this->assertNull($ua->meta);
        $this->assertFalse($ua->hasMeta());
    }

    public function test_creates_with_metadata(): void
    {
        $uaString = $this->faker->userAgent();
        $meta = new Meta(device: DeviceType::Desktop);
        $ua = new UserAgent($uaString, $meta);

        $this->assertSame($uaString, $ua->uaString);
        $this->assertSame($meta, $ua->meta);
        $this->assertTrue($ua->hasMeta());
    }

    public function test_from_string_named_constructor(): void
    {
        $uaString = $this->faker->userAgent();
        $ua = UserAgent::fromString($uaString);

        $this->assertSame($uaString, $ua->uaString);
        $this->assertNull($ua->meta);
    }

    public function test_with_meta_named_constructor(): void
    {
        $uaString = $this->faker->userAgent();
        $meta = new Meta(browser: BrowserFamily::Chrome);
        $ua = UserAgent::withMeta($uaString, $meta);

        $this->assertSame($uaString, $ua->uaString);
        $this->assertSame($meta, $ua->meta);
    }

    public function test_to_string_returns_ua_string(): void
    {
        $uaString = $this->faker->userAgent();
        $ua = new UserAgent($uaString);

        $this->assertSame($uaString, $ua->toString());
    }

    public function test_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User-agent string cannot be empty');

        new UserAgent('');
    }

    public function test_rejects_whitespace_only_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User-agent string cannot be empty');

        new UserAgent('   ');
    }

    public function test_is_readonly(): void
    {
        $ua = new UserAgent($this->faker->userAgent());
        $reflection = new ReflectionClass($ua);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_factory_generates_valid_user_agents(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $ua = $this->factory->make();

            $this->assertInstanceOf(UserAgent::class, $ua);
            $this->assertNotEmpty($ua->uaString);
        }
    }

    public function test_factory_generates_user_agent_with_specific_device(): void
    {
        $ua = $this->factory->makeWithDevice(DeviceType::Mobile);

        $this->assertTrue($ua->hasMeta());
        $this->assertNotNull($ua->meta);
        $this->assertSame(DeviceType::Mobile, $ua->meta->device);
    }
}
