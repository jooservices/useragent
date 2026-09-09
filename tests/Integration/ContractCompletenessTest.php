<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Integration;

use JOOservices\UserAgent\Core\Renderer;
use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\GenerationResult;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Exceptions\RenderException;
use JOOservices\UserAgent\Tests\Support\FixtureDataset;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ContractCompletenessTest extends TestCase
{
    #[Test]
    public function test_DTO_H04_H06_result_shape_and_json_round_trip(): void
    {
        $result = FixtureDataset::generator()->generate(new GenerationRequest(seed: 42));
        self::assertSame(
            ['userAgent', 'profile', 'provenance', 'seed', 'selection', 'candidateCount'],
            array_keys($result->toArray()),
        );
        $decodedResult = json_decode($result->toJson(), true, 32, JSON_THROW_ON_ERROR);
        self::assertIsArray($decodedResult);
        self::assertFalse(array_is_list($decodedResult));
        /** @var array<string, mixed> $decodedResult */
        $copy = GenerationResult::from($decodedResult);
        self::assertSame($result->toArray(), $copy->toArray());
        self::assertSame('fixture-1', $copy->profile->provenance->revision);
    }

    #[Test]
    public function test_INV01_INV04_INV05_two_hundred_profiles_match_rendered_output(): void
    {
        $generator = FixtureDataset::generator();
        $renderer = new Renderer();
        foreach (range(1, 200) as $seed) {
            $result = $generator->generate(new GenerationRequest(seed: $seed));
            self::assertTrue($renderer->profileMatches($result->userAgent, $result->profile));
            self::assertDoesNotMatchRegularExpression('/[\{\r\n\0]/', $result->userAgent);
        }
    }

    #[Test]
    public function test_GEN_W02_locale_is_metadata_only(): void
    {
        $base = [
            'browser' => BrowserFamily::Chrome,
            'device' => DeviceClass::Desktop,
            'platform' => Platform::Windows,
            'seed' => 42,
        ];
        $without = FixtureDataset::generator()->generate(new GenerationRequest(...$base));
        $with = FixtureDataset::generator()->generate(new GenerationRequest(...$base, locale: 'fr-FR'));
        self::assertSame($without->userAgent, $with->userAgent);
        self::assertSame('fr-FR', $with->profile->locale);
        self::assertFalse($with->profile->localeRendered);
    }

    /** @return iterable<string, array{string}> */
    public static function unsafeTemplate(): iterable
    {
        yield 'missing model' => ['{fullVersion} {osToken} {model}'];
        yield 'leftover placeholder' => ['{fullVersion} {osToken} {foo}'];
        yield 'header injection' => ["{fullVersion} {osToken}\r\nInjected"];
        yield 'length cap' => ['{fullVersion} {osToken} ' . str_repeat('x', 513)];
    }

    #[Test]
    #[DataProvider('unsafeTemplate')]
    public function test_REN_U_W_SEC_renderer_fails_closed(string $template): void
    {
        $profile = FixtureDataset::generator()->generate(new GenerationRequest(
            browser: BrowserFamily::Chrome,
            device: DeviceClass::Desktop,
            platform: Platform::Windows,
            seed: 1,
        ))->profile;
        $this->expectException(RenderException::class);
        (new Renderer())->render($template, $profile);
    }

    #[Test]
    public function test_CLI_integration_binary_returns_stable_json_array(): void
    {
        $pipes = [];
        $process = proc_open(
            [PHP_BINARY, dirname(__DIR__, 2) . '/bin/useragent', '--seed=42', '--format=json'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            dirname(__DIR__, 2),
        );
        self::assertIsResource($process);
        self::assertIsResource($pipes[1]);
        self::assertIsResource($pipes[2]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process));
        self::assertSame('', $stderr);
        self::assertIsString($stdout);
        $decoded = json_decode($stdout, true, 32, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertCount(1, $decoded);
    }

    #[Test]
    public function test_DOC01_DOC04_documentation_and_package_claims_are_honest(): void
    {
        $root = dirname(__DIR__, 2);
        $readme = file_get_contents($root . '/README.md');
        $upgrade = file_get_contents($root . '/UPGRADE-4.0.md');
        $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 32, JSON_THROW_ON_ERROR);
        self::assertIsString($readme);
        self::assertIsString($upgrade);
        self::assertStringContainsStringIgnoringCase('synthetic', $readme);
        self::assertStringContainsStringIgnoringCase('dataset', $readme);
        self::assertDoesNotMatchRegularExpression('/scraper|stealth|undetectable|googlebot|crawler-safe/i', $readme);
        self::assertStringContainsString('GenerationSpec', $upgrade);
        self::assertIsArray($composer);
        self::assertArrayNotHasKey('version', $composer);
    }
}
