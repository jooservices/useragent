<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Pickers;

use JOOservices\UserAgent\Exceptions\InvalidSpecException;
use JOOservices\UserAgent\Pickers\VersionPicker;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Templates\Browsers\ChromeTemplate;
use PHPUnit\Framework\TestCase;

final class VersionPickerTest extends TestCase
{
    private VersionPicker $picker;

    private ChromeTemplate $template;

    protected function setUp(): void
    {
        $this->picker = new VersionPicker();
        $this->template = new ChromeTemplate();
    }

    public function test_it_picks_exact_version_when_specified(): void
    {
        $spec = GenerationSpec::create()
            ->versionExact(120)
            ->build();

        $version = $this->picker->pick($this->template, $spec);

        $this->assertSame(120, $version);
    }

    public function test_it_throws_when_exact_version_below_minimum(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('outside template range');

        $spec = GenerationSpec::create()
            ->versionExact(50) // Below Chrome min (90)
            ->build();

        $this->picker->pick($this->template, $spec);
    }

    public function test_it_throws_when_exact_version_above_maximum(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('outside template range');

        $spec = GenerationSpec::create()
            ->versionExact(200) // Above Chrome max (145)
            ->build();

        $this->picker->pick($this->template, $spec);
    }

    public function test_it_picks_within_range_when_min_and_max_specified(): void
    {
        $spec = GenerationSpec::create()
            ->versionMin(100)
            ->versionMax(110)
            ->build();

        $version = $this->picker->pick($this->template, $spec, 12345);

        $this->assertGreaterThanOrEqual(100, $version);
        $this->assertLessThanOrEqual(110, $version);
    }

    public function test_it_picks_within_range_with_only_min_specified(): void
    {
        $spec = GenerationSpec::create()
            ->versionMin(100)
            ->build();

        $version = $this->picker->pick($this->template, $spec, 12345);

        $this->assertGreaterThanOrEqual(100, $version);
        $this->assertLessThanOrEqual($this->template->getMaxVersion(), $version);
    }

    public function test_it_picks_within_range_with_only_max_specified(): void
    {
        $spec = GenerationSpec::create()
            ->versionMax(110)
            ->build();

        $version = $this->picker->pick($this->template, $spec, 12345);

        $this->assertGreaterThanOrEqual($this->template->getMinVersion(), $version);
        $this->assertLessThanOrEqual(110, $version);
    }

    public function test_it_throws_when_min_below_template_minimum(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('below template minimum');

        $spec = GenerationSpec::create()
            ->versionMin(50) // Below Chrome min (90)
            ->versionMax(100)
            ->build();

        $this->picker->pick($this->template, $spec);
    }

    public function test_it_throws_when_max_above_template_maximum(): void
    {
        $this->expectException(InvalidSpecException::class);
        $this->expectExceptionMessage('above template maximum');

        $spec = GenerationSpec::create()
            ->versionMin(100)
            ->versionMax(200) // Above Chrome max (145)
            ->build();

        $this->picker->pick($this->template, $spec);
    }

    public function test_it_picks_stable_version_when_no_constraints(): void
    {
        $spec = new GenerationSpec();

        $version = $this->picker->pick($this->template, $spec);

        $this->assertSame($this->template->getStableVersion(), $version);
    }

    public function test_it_picks_deterministically_with_seed(): void
    {
        $spec = GenerationSpec::create()
            ->versionMin(100)
            ->versionMax(110)
            ->build();

        $version1 = $this->picker->pick($this->template, $spec, 54321);
        $version2 = $this->picker->pick($this->template, $spec, 54321);

        $this->assertSame($version1, $version2);
    }

    public function test_it_picks_weighted_recent_prefers_newer_versions(): void
    {
        // Run multiple times and check distribution
        $results = [];
        for ($i = 0; $i < 100; $i++) {
            $results[] = $this->picker->pickWeightedRecent(1, 10);
        }

        $avg = array_sum($results) / count($results);

        // Average should be > 5.5 (midpoint) due to recency weighting
        $this->assertGreaterThan(5.5, $avg);
    }

    public function test_it_picks_weighted_recent_deterministically_with_seed(): void
    {
        $version1 = $this->picker->pickWeightedRecent(1, 10, 99999);
        $version2 = $this->picker->pickWeightedRecent(1, 10, 99999);

        $this->assertSame($version1, $version2);
    }

    public function test_it_picks_weighted_recent_within_range(): void
    {
        $version = $this->picker->pickWeightedRecent(50, 60, 12345);

        $this->assertGreaterThanOrEqual(50, $version);
        $this->assertLessThanOrEqual(60, $version);
    }

    public function test_it_picks_weighted_recent_returns_max_as_fallback(): void
    {
        // Single version range
        $version = $this->picker->pickWeightedRecent(100, 100);

        $this->assertSame(100, $version);
    }
}
