<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Validation;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Exceptions\InvalidSpecException;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Spec\RandomSpec;
use JOOservices\UserAgent\Validation\SpecValidator;
use PHPUnit\Framework\TestCase;

final class SpecValidatorTest extends TestCase
{
    private SpecValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SpecValidator;
    }

    // ========== HAPPY PATH TESTS ==========

    /** @test */
    public function test_it_validates_empty_spec(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = new GenerationSpec;

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_validates_valid_spec_with_all_properties(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->device(DeviceType::Desktop)
            ->versionMin(110)
            ->versionMax(120)
            ->channel('stable')
            ->locale('en-US')
            ->arch('x86_64')
            ->tags(['popular'])
            ->randomSpec(new RandomSpec(seed: 12345))
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_validates_version_exact_without_range(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->versionExact(115)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_validates_all_valid_channels(): void
    {
        $this->expectNotToPerformAssertions();
        $channels = ['stable', 'beta', 'dev', 'canary'];

        foreach ($channels as $channel) {
            $spec = GenerationSpec::create()
                ->channel($channel)
                ->build();

            $this->validator->validate($spec);
        }
    }

    /** @test */
    public function test_it_validates_all_valid_architectures(): void
    {
        $this->expectNotToPerformAssertions();
        $architectures = ['x86_64', 'x64', 'ARM', 'ARM64', 'WOW64', 'i686'];

        foreach ($architectures as $arch) {
            $spec = GenerationSpec::create()
                ->arch($arch)
                ->build();

            $this->validator->validate($spec);
        }
    }

    /** @test */
    public function test_it_validates_various_locale_formats(): void
    {
        $this->expectNotToPerformAssertions();
        $locales = ['en-US', 'fr-FR', 'de-DE', 'ja-JP', 'zh-CN', 'pt-BR', 'en', 'fr'];

        foreach ($locales as $locale) {
            $spec = GenerationSpec::create()
                ->locale($locale)
                ->build();

            $this->validator->validate($spec);
        }
    }

    // ========== UNHAPPY PATH TESTS - VERSION VALIDATION ==========

    /** @test */
    public function test_it_throws_when_version_min_greater_than_max(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionMin (120) cannot be greater than versionMax (110)');

        $spec = GenerationSpec::create()
            ->versionMin(120)
            ->versionMax(110)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_exact_version_conflicts_with_min(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('Cannot use versionExact with versionMin/versionMax');

        $spec = GenerationSpec::create()
            ->versionExact(120)
            ->versionMin(110)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_exact_version_conflicts_with_max(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('Cannot use versionExact with versionMin/versionMax');

        $spec = GenerationSpec::create()
            ->versionExact(120)
            ->versionMax(130)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_exact_version_conflicts_with_range(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('Cannot use versionExact with versionMin/versionMax');

        $spec = GenerationSpec::create()
            ->versionExact(120)
            ->versionMin(110)
            ->versionMax(130)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_version_min_is_zero(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionMin must be >= 1, got 0');

        $spec = GenerationSpec::create()
            ->versionMin(0)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_version_min_is_negative(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionMin must be >= 1, got -1');

        $spec = GenerationSpec::create()
            ->versionMin(-1)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_version_max_is_zero(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionMax must be >= 1, got 0');

        $spec = GenerationSpec::create()
            ->versionMax(0)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_version_max_is_negative(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionMax must be >= 1, got -5');

        $spec = GenerationSpec::create()
            ->versionMax(-5)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_version_exact_is_zero(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionExact must be >= 1, got 0');

        $spec = GenerationSpec::create()
            ->versionExact(0)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_version_exact_is_negative(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionExact must be >= 1, got -10');

        $spec = GenerationSpec::create()
            ->versionExact(-10)
            ->build();

        $this->validator->validate($spec);
    }

    // ========== SECURITY TESTS - VERSION OVERFLOW PROTECTION ==========

    /** @test */
    public function test_it_throws_when_version_min_exceeds_maximum(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionMin too high (1000), maximum is 999');

        $spec = GenerationSpec::create()
            ->versionMin(1000)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_version_max_exceeds_maximum(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionMax too high (5000), maximum is 999');

        $spec = GenerationSpec::create()
            ->versionMax(5000)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_version_exact_exceeds_maximum(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('versionExact too high (10000), maximum is 999');

        $spec = GenerationSpec::create()
            ->versionExact(10000)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_accepts_version_999(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->versionMin(1)
            ->versionMax(999)
            ->build();

        $this->validator->validate($spec);
    }

    // ========== UNHAPPY PATH TESTS - CHANNEL VALIDATION ==========

    /** @test */
    public function test_it_throws_when_channel_is_invalid(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid channel 'nightly'. Must be one of: stable, beta, dev, canary");

        $spec = GenerationSpec::create()
            ->channel('nightly')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_channel_is_empty_string(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid channel ''. Must be one of:");

        $spec = GenerationSpec::create()
            ->channel('')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_channel_has_wrong_case(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid channel 'Stable'. Must be one of:");

        $spec = GenerationSpec::create()
            ->channel('Stable') // Should be lowercase
            ->build();

        $this->validator->validate($spec);
    }

    // ========== UNHAPPY PATH TESTS - ARCH VALIDATION ==========

    /** @test */
    public function test_it_throws_when_arch_is_invalid(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid arch 'x86'. Must be one of:");

        $spec = GenerationSpec::create()
            ->arch('x86')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_arch_is_empty_string(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid arch ''. Must be one of:");

        $spec = GenerationSpec::create()
            ->arch('')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_arch_has_wrong_case(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid arch 'arm'. Must be one of:");

        $spec = GenerationSpec::create()
            ->arch('arm') // Should be uppercase ARM
            ->build();

        $this->validator->validate($spec);
    }

    // ========== UNHAPPY PATH TESTS - LOCALE VALIDATION ==========

    /** @test */
    public function test_it_throws_when_locale_is_invalid_format(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid locale format 'english'. Expected format: 'en-US', 'fr-FR', etc.");

        $spec = GenerationSpec::create()
            ->locale('english')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_locale_has_numbers(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid locale format 'en-123'");

        $spec = GenerationSpec::create()
            ->locale('en-123')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_locale_is_too_long(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid locale format 'english-UNITED'");

        $spec = GenerationSpec::create()
            ->locale('english-UNITED')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_locale_is_empty_string(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage("Invalid locale format ''");

        $spec = GenerationSpec::create()
            ->locale('')
            ->build();

        $this->validator->validate($spec);
    }

    // ========== SECURITY TESTS - TAG VALIDATION ==========

    /** @test */
    public function test_it_throws_when_tag_is_empty_string(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('Tag must be non-empty string');

        $spec = GenerationSpec::create()
            ->tags(['valid', ''])
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_tag_is_whitespace_only(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('Tag must be non-empty string');

        $spec = GenerationSpec::create()
            ->tags(['valid', '   '])
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_tag_is_not_string(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('Tag must be non-empty string, got int');

        $spec = new GenerationSpec(tags: ['valid', 123]);

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_tag_is_null(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('Tag must be non-empty string, got null');

        $spec = new GenerationSpec(tags: ['valid', null]);

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_tag_is_array(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('Tag must be non-empty string, got array');

        $spec = new GenerationSpec(tags: ['valid', ['nested']]);

        $this->validator->validate($spec);
    }

    // ========== UNHAPPY PATH TESTS - RANDOM SPEC VALIDATION ==========

    /** @test */
    public function test_it_throws_when_history_window_is_zero(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('historyWindow must be >= 1, got 0');

        $spec = GenerationSpec::create()
            ->randomSpec(new RandomSpec(historyWindow: 0))
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_history_window_is_negative(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('historyWindow must be >= 1, got -10');

        $spec = GenerationSpec::create()
            ->randomSpec(new RandomSpec(historyWindow: -10))
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_history_window_is_too_large(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('historyWindow too large (10001), maximum is 10000');

        $spec = GenerationSpec::create()
            ->randomSpec(new RandomSpec(historyWindow: 10001))
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_retry_budget_is_negative(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('retryBudget must be >= 0, got -1');

        $spec = GenerationSpec::create()
            ->randomSpec(new RandomSpec(retryBudget: -1))
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_retry_budget_is_too_large(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('retryBudget too large (101), maximum is 100');

        $spec = GenerationSpec::create()
            ->randomSpec(new RandomSpec(retryBudget: 101))
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_throws_when_seed_is_negative(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('seed must be >= 0, got -1');

        $spec = GenerationSpec::create()
            ->randomSpec(new RandomSpec(seed: -1))
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_accepts_zero_seed(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->randomSpec(new RandomSpec(seed: 0))
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_accepts_zero_retry_budget(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->randomSpec(new RandomSpec(retryBudget: 0))
            ->build();

        $this->validator->validate($spec);
    }

    // ========== EDGE CASE TESTS ==========

    /** @test */
    public function test_it_validates_minimum_version_value(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->versionMin(1)
            ->versionMax(1)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_validates_same_min_and_max_version(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->versionMin(115)
            ->versionMax(115)
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_validates_large_version_range(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->versionMin(1)
            ->versionMax(999)
            ->build();

        $this->validator->validate($spec);
    }

    // ========== EXPLOIT/ATTACK TESTS ==========

    /** @test */
    public function test_it_prevents_sql_injection_in_channel(): void
    {
        $this->expectException(InvalidSpecException::class);

        $spec = GenerationSpec::create()
            ->channel("'; DROP TABLE users; --")
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_prevents_xss_in_locale(): void
    {
        $this->expectException(InvalidSpecException::class);

        $spec = GenerationSpec::create()
            ->locale("<script>alert('xss')</script>")
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_prevents_path_traversal_in_arch(): void
    {
        $this->expectException(InvalidSpecException::class);

        $spec = GenerationSpec::create()
            ->arch('../../etc/passwd')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_prevents_command_injection_in_channel(): void
    {
        $this->expectException(InvalidSpecException::class);

        $spec = GenerationSpec::create()
            ->channel('stable; rm -rf /')
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_prevents_null_byte_injection_in_locale(): void
    {
        $this->expectException(InvalidSpecException::class);

        $spec = GenerationSpec::create()
            ->locale("en-US\0malicious")
            ->build();

        $this->validator->validate($spec);
    }

    // ========== WEIRD/STRANGE TESTS ==========

    /** @test */
    public function test_it_handles_unicode_in_tags(): void
    {
        $this->expectNotToPerformAssertions();
        $spec = GenerationSpec::create()
            ->tags(['popular', '日本語', 'émoji🎉'])
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_handles_very_long_tag_names(): void
    {
        $this->expectNotToPerformAssertions();
        $longTag = str_repeat('a', 1000);

        $spec = GenerationSpec::create()
            ->tags([$longTag])
            ->build();

        $this->validator->validate($spec);
    }

    /** @test */
    public function test_it_handles_many_tags(): void
    {
        $this->expectNotToPerformAssertions();
        $manyTags = array_map(fn ($i) => "tag{$i}", range(1, 1000));

        $spec = GenerationSpec::create()
            ->tags($manyTags)
            ->build();

        $this->validator->validate($spec);
    }
}
