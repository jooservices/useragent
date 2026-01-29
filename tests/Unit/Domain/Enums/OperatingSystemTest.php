<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\Enums;

use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use PHPUnit\Framework\TestCase;

final class OperatingSystemTest extends TestCase
{
    public function test_it_has_all_os_cases(): void
    {
        $systems = OperatingSystem::cases();

        $this->assertCount(7, $systems);
        $this->assertContains(OperatingSystem::Windows, $systems);
        $this->assertContains(OperatingSystem::MacOS, $systems);
        $this->assertContains(OperatingSystem::Linux, $systems);
        $this->assertContains(OperatingSystem::Android, $systems);
        $this->assertContains(OperatingSystem::iOS, $systems);
        $this->assertContains(OperatingSystem::ChromeOS, $systems);
        $this->assertContains(OperatingSystem::Other, $systems);
    }

    public function test_it_has_correct_backing_values(): void
    {
        $this->assertSame('windows', OperatingSystem::Windows->value);
        $this->assertSame('macos', OperatingSystem::MacOS->value);
        $this->assertSame('linux', OperatingSystem::Linux->value);
        $this->assertSame('android', OperatingSystem::Android->value);
        $this->assertSame('ios', OperatingSystem::iOS->value);
        $this->assertSame('chromeos', OperatingSystem::ChromeOS->value);
        $this->assertSame('other', OperatingSystem::Other->value);
    }

    public function test_it_can_be_created_from_string(): void
    {
        $this->assertSame(OperatingSystem::Windows, OperatingSystem::from('windows'));
        $this->assertSame(OperatingSystem::MacOS, OperatingSystem::from('macos'));
        $this->assertSame(OperatingSystem::Linux, OperatingSystem::from('linux'));
        $this->assertSame(OperatingSystem::Android, OperatingSystem::from('android'));
        $this->assertSame(OperatingSystem::iOS, OperatingSystem::from('ios'));
        $this->assertSame(OperatingSystem::ChromeOS, OperatingSystem::from('chromeos'));
        $this->assertSame(OperatingSystem::Other, OperatingSystem::from('other'));
    }

    public function test_it_returns_correct_labels(): void
    {
        $this->assertSame('Windows', OperatingSystem::Windows->label());
        $this->assertSame('macOS', OperatingSystem::MacOS->label());
        $this->assertSame('Linux', OperatingSystem::Linux->label());
        $this->assertSame('Android', OperatingSystem::Android->label());
        $this->assertSame('iOS', OperatingSystem::iOS->label());
        $this->assertSame('ChromeOS', OperatingSystem::ChromeOS->label());
        $this->assertSame('Other', OperatingSystem::Other->label());
    }

    public function test_label_method_covers_all_cases(): void
    {
        foreach (OperatingSystem::cases() as $os) {
            $label = $os->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }
}
