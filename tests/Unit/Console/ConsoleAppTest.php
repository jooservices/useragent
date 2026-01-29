<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Console;

use JOOservices\UserAgent\Console\ConsoleApp;
use PHPUnit\Framework\TestCase;

class ConsoleAppTest extends TestCase
{
    public function test_run_generates_user_agent(): void
    {
        $app = new ConsoleApp(['script.php', '--count=1']);

        ob_start();
        $exitCode = $app->run();
        $output = ob_get_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Mozilla/5.0', $output);
    }

    public function test_run_generates_multiple_user_agents(): void
    {
        $app = new ConsoleApp(['script.php', '--count=2']);

        ob_start();
        $exitCode = $app->run();
        $output = ob_get_clean();

        $this->assertEquals(0, $exitCode);
        $lines = array_filter(explode(PHP_EOL, $output));
        $this->assertCount(2, $lines);
    }

    public function test_browser_argument(): void
    {
        // Use firefox, ensuring we get a Firefox UA
        $app = new ConsoleApp(['script.php', '--browser=firefox', '--count=1']);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Firefox', $output);
    }

    public function test_device_argument(): void
    {
        $app = new ConsoleApp(['script.php', '--device=mobile', '--count=1']);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        // Check for common mobile indicators
        $isMobile = str_contains($output, 'Mobile') || str_contains($output, 'Android') || str_contains($output, 'iPhone');
        $this->assertTrue($isMobile, 'Output should resemble a mobile user agent: ' . $output);
    }

    public function test_os_argument_mapping(): void
    {
        // win -> Windows
        $app = new ConsoleApp(['script.php', '--os=win', '--count=1']);
        ob_start();
        $app->run();
        $outputWin = ob_get_clean();
        $this->assertStringContainsString('Windows', $outputWin);

        // linux -> Linux
        $app = new ConsoleApp(['script.php', '--os=linux', '--count=1']);
        ob_start();
        $app->run();
        $outputLinux = ob_get_clean();
        $this->assertStringContainsString('Linux', $outputLinux);
    }

    public function test_os_argument_capitalization(): void
    {
        // WINDOWS -> Windows (mapOsName handles case)
        $app = new ConsoleApp(['script.php', '--os=WINDOWS']);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Windows', $output);
    }

    public function test_invalid_browser_graceful_fallback(): void
    {
        $app = new ConsoleApp(['script.php', '--browser=InvalidBrowser']);

        ob_start();
        $exitCode = $app->run();
        $output = ob_get_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertNotEmpty($output);
    }

    public function test_exploit_attemp_with_huge_count(): void
    {
        $app = new ConsoleApp(['script.php', '--count=999999']);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $lines = array_filter(explode(PHP_EOL, $output));
        // Should be capped at 100
        $this->assertCount(100, $lines);
    }

    public function test_negative_count(): void
    {
        $app = new ConsoleApp(['script.php', '--count=-5']);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        // Should be clamped to 1
        $lines = array_filter(explode(PHP_EOL, $output));
        $this->assertCount(1, $lines);
    }

    public function test_empty_args(): void
    {
        $app = new ConsoleApp(['script.php']);

        ob_start();
        $exitCode = $app->run();
        $output = ob_get_clean();

        $this->assertEquals(0, $exitCode);
        $this->assertNotEmpty($output);
    }
}
