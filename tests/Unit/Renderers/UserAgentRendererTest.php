<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Renderers;

use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Renderers\UserAgentRenderer;
use JOOservices\UserAgent\Templates\Browsers\ChromeTemplate;
use PHPUnit\Framework\TestCase;

final class UserAgentRendererTest extends TestCase
{
    private UserAgentRenderer $renderer;

    private ChromeTemplate $template;

    protected function setUp(): void
    {
        $this->renderer = new UserAgentRenderer();
        $this->template = new ChromeTemplate();
    }

    public function test_it_renders_desktop_user_agent(): void
    {
        $context = [
            'version' => 120,
            'browserVersion' => 120,
            'locale' => 'en-US',
            'arch' => 'x86_64',
            'osVersion' => '10.0',
        ];

        $ua = $this->renderer->render(
            $this->template,
            DeviceType::Desktop,
            OperatingSystem::Windows,
            $context
        );

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('120', $ua);
    }

    public function test_it_renders_mobile_user_agent(): void
    {
        $context = [
            'version' => 120,
            'browserVersion' => 120,
            'locale' => 'en-US',
            'model' => 'SM-G998B',
            'osVersion' => '14',
        ];

        $ua = $this->renderer->render(
            $this->template,
            DeviceType::Mobile,
            OperatingSystem::Android,
            $context
        );

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('120', $ua);
    }

    public function test_it_renders_tablet_user_agent(): void
    {
        $context = [
            'version' => 120,
            'browserVersion' => 120,
            'locale' => 'en-US',
            'model' => 'iPad Pro',
            'osVersion' => '17.0',
        ];

        $ua = $this->renderer->render(
            $this->template,
            DeviceType::Tablet,
            OperatingSystem::iOS,
            $context
        );

        $this->assertNotEmpty($ua);
    }

    public function test_it_returns_empty_string_for_bot_device(): void
    {
        $context = ['version' => 120];

        $ua = $this->renderer->render(
            $this->template,
            DeviceType::Bot,
            OperatingSystem::Other,
            $context
        );

        $this->assertSame('', $ua);
    }

    public function test_it_replaces_all_placeholders(): void
    {
        $template = 'Mozilla/{version} (Platform; {locale}) Engine/{browserVersion}';
        $context = [
            'version' => '5.0',
            'locale' => 'en-US',
            'browserVersion' => '120',
        ];

        $result = $this->renderer->render(
            $this->template,
            DeviceType::Desktop,
            OperatingSystem::Windows,
            $context
        );

        // Should not contain any unreplaced placeholders
        $this->assertStringNotContainsString('{', $result);
        $this->assertStringNotContainsString('}', $result);
    }

    public function test_it_extracts_placeholders_from_template(): void
    {
        $template = 'Mozilla/{version} ({locale}; {arch})';

        $placeholders = $this->renderer->extractPlaceholders($template);

        $this->assertCount(3, $placeholders);
        $this->assertContains('version', $placeholders);
        $this->assertContains('locale', $placeholders);
        $this->assertContains('arch', $placeholders);
    }

    public function test_it_returns_empty_array_for_template_without_placeholders(): void
    {
        $template = 'Mozilla/5.0 (Windows NT 10.0)';

        $placeholders = $this->renderer->extractPlaceholders($template);

        $this->assertEmpty($placeholders);
    }

    public function test_it_handles_missing_context_values(): void
    {
        $context = ['version' => 120]; // Missing other values

        $ua = $this->renderer->render(
            $this->template,
            DeviceType::Desktop,
            OperatingSystem::Windows,
            $context
        );

        // Should still render, but may have unreplaced placeholders
        $this->assertNotEmpty($ua);
    }
}
