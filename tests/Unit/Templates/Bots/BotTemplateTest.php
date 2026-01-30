<?php

declare(strict_types=1);

namespace Tests\Unit\Templates\Bots;

use JOOservices\UserAgent\Domain\Enums\BotType;
use JOOservices\UserAgent\Templates\Bots\BotTemplate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BotTemplate::class)]
#[CoversClass(BotType::class)]
final class BotTemplateTest extends TestCase
{
    private BotTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->template = new BotTemplate;
    }

    public function test_generate_googlebot(): void
    {
        $ua = $this->template->generate(BotType::Googlebot);
        $this->assertStringContainsString('Googlebot', $ua);
        $this->assertStringContainsString('google.com', $ua);
    }

    public function test_generate_googlebot_mobile(): void
    {
        $ua = $this->template->generate(BotType::Googlebot, true);
        $this->assertStringContainsString('Googlebot', $ua);
        $this->assertStringContainsString('Android', $ua);
        $this->assertStringContainsString('Mobile', $ua);
    }

    public function test_generate_bingbot(): void
    {
        $ua = $this->template->generate(BotType::Bingbot);
        $this->assertStringContainsString('bingbot', $ua);
        $this->assertStringContainsString('bing.com', $ua);
    }

    public function test_generate_bingbot_mobile(): void
    {
        $ua = $this->template->generate(BotType::Bingbot, true);
        $this->assertStringContainsString('bingbot', $ua);
        $this->assertStringContainsString('Mobile', $ua);
    }

    #[DataProvider('botTypeProvider')]
    public function test_generate_all_bot_types(BotType $botType): void
    {
        $ua = $this->template->generate($botType);
        $this->assertNotEmpty($ua);
        $this->assertIsString($ua);
    }

    /**
     * @return array<string, array{BotType}>
     */
    public static function botTypeProvider(): array
    {
        $result = [];
        foreach (BotType::cases() as $botType) {
            $result[$botType->value] = [$botType];
        }

        return $result;
    }

    public function test_generate_random_bot(): void
    {
        $ua = $this->template->generateRandom();
        $this->assertNotEmpty($ua);
        $this->assertIsString($ua);
    }

    public function test_generate_random_bot_with_seed_is_deterministic(): void
    {
        $seed = 12345;
        $ua1 = $this->template->generateRandom($seed);
        $ua2 = $this->template->generateRandom($seed);
        $this->assertEquals($ua1, $ua2);
    }

    public function test_get_available_bots(): void
    {
        $bots = $this->template->getAvailableBots();
        $this->assertCount(10, $bots);
        $this->assertContainsOnlyInstancesOf(BotType::class, $bots);
    }

    public function test_bot_type_labels(): void
    {
        $this->assertEquals('Googlebot', BotType::Googlebot->label());
        $this->assertEquals('Bingbot', BotType::Bingbot->label());
        $this->assertEquals('YandexBot', BotType::YandexBot->label());
    }

    public function test_yandex_bot(): void
    {
        $ua = $this->template->generate(BotType::YandexBot);
        $this->assertStringContainsString('YandexBot', $ua);
        $this->assertStringContainsString('yandex.com', $ua);
    }

    public function test_baidu_spider(): void
    {
        $ua = $this->template->generate(BotType::Baiduspider);
        $this->assertStringContainsString('Baiduspider', $ua);
        $this->assertStringContainsString('baidu.com', $ua);
    }

    public function test_duckduckbot(): void
    {
        $ua = $this->template->generate(BotType::DuckDuckBot);
        $this->assertStringContainsString('DuckDuckBot', $ua);
        $this->assertStringContainsString('duckduckgo.com', $ua);
    }

    public function test_facebook_bot(): void
    {
        $ua = $this->template->generate(BotType::FacebookBot);
        $this->assertStringContainsString('facebookexternalhit', $ua);
        $this->assertStringContainsString('facebook.com', $ua);
    }

    public function test_twitter_bot(): void
    {
        $ua = $this->template->generate(BotType::TwitterBot);
        $this->assertStringContainsString('Twitterbot', $ua);
    }

    public function test_linkedin_bot(): void
    {
        $ua = $this->template->generate(BotType::LinkedInBot);
        $this->assertStringContainsString('LinkedInBot', $ua);
        $this->assertStringContainsString('linkedin.com', $ua);
    }

    public function test_applebot(): void
    {
        $ua = $this->template->generate(BotType::AppleBot);
        $this->assertStringContainsString('Applebot', $ua);
    }
}
