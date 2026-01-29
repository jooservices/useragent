<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\Enums;

use JOOservices\UserAgent\Domain\Enums\Engine;
use PHPUnit\Framework\TestCase;

final class EngineTest extends TestCase
{
    public function test_it_has_all_engine_cases(): void
    {
        $engines = Engine::cases();

        $this->assertCount(6, $engines);
        $this->assertContains(Engine::Blink, $engines);
        $this->assertContains(Engine::Gecko, $engines);
        $this->assertContains(Engine::WebKit, $engines);
        $this->assertContains(Engine::Trident, $engines);
        $this->assertContains(Engine::EdgeHTML, $engines);
        $this->assertContains(Engine::Other, $engines);
    }

    public function test_it_has_correct_backing_values(): void
    {
        $this->assertSame('blink', Engine::Blink->value);
        $this->assertSame('gecko', Engine::Gecko->value);
        $this->assertSame('webkit', Engine::WebKit->value);
        $this->assertSame('trident', Engine::Trident->value);
        $this->assertSame('edgehtml', Engine::EdgeHTML->value);
        $this->assertSame('other', Engine::Other->value);
    }

    public function test_it_can_be_created_from_string(): void
    {
        $this->assertSame(Engine::Blink, Engine::from('blink'));
        $this->assertSame(Engine::Gecko, Engine::from('gecko'));
        $this->assertSame(Engine::WebKit, Engine::from('webkit'));
        $this->assertSame(Engine::Trident, Engine::from('trident'));
        $this->assertSame(Engine::EdgeHTML, Engine::from('edgehtml'));
        $this->assertSame(Engine::Other, Engine::from('other'));
    }

    public function test_it_returns_correct_labels(): void
    {
        $this->assertSame('Blink', Engine::Blink->label());
        $this->assertSame('Gecko', Engine::Gecko->label());
        $this->assertSame('WebKit', Engine::WebKit->label());
        $this->assertSame('Trident', Engine::Trident->label());
        $this->assertSame('EdgeHTML', Engine::EdgeHTML->label());
        $this->assertSame('Other', Engine::Other->label());
    }

    public function test_label_method_covers_all_cases(): void
    {
        foreach (Engine::cases() as $engine) {
            $label = $engine->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }
}
