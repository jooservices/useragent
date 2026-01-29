<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Pickers;

use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Pickers\ArchPicker;
use JOOservices\UserAgent\Pickers\LocalePicker;
use JOOservices\UserAgent\Pickers\ModelPicker;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Templates\Catalogs\ArchCatalog;
use JOOservices\UserAgent\Templates\Catalogs\LocaleCatalog;
use PHPUnit\Framework\TestCase;

/**
 * Consolidated tests for Model, Locale, and Arch pickers.
 */
final class PickersTest extends TestCase
{
    // ========== MODEL PICKER TESTS ==========

    public function test_model_picker_returns_android_model_for_android_os(): void
    {
        $picker = new ModelPicker();
        $spec = new GenerationSpec();

        $model = $picker->pick(OperatingSystem::Android, $spec, 12345);

        $this->assertNotEmpty($model);
        $this->assertNotSame('Unknown', $model);
    }

    public function test_model_picker_returns_ios_model_for_ios_os(): void
    {
        $picker = new ModelPicker();
        $spec = new GenerationSpec();

        $model = $picker->pick(OperatingSystem::iOS, $spec, 12345);

        $this->assertNotEmpty($model);
        $this->assertNotSame('Unknown', $model);
    }

    public function test_model_picker_returns_unknown_for_unsupported_os(): void
    {
        $picker = new ModelPicker();
        $spec = new GenerationSpec();

        $model = $picker->pick(OperatingSystem::Windows, $spec);

        $this->assertSame('Unknown', $model);
    }

    public function test_model_picker_is_deterministic_with_seed(): void
    {
        $picker = new ModelPicker();
        $spec = new GenerationSpec();

        $model1 = $picker->pick(OperatingSystem::Android, $spec, 99999);
        $model2 = $picker->pick(OperatingSystem::Android, $spec, 99999);

        $this->assertSame($model1, $model2);
    }

    // ========== LOCALE PICKER TESTS ==========

    public function test_locale_picker_uses_spec_locale_when_provided(): void
    {
        $picker = new LocalePicker();
        $spec = GenerationSpec::create()
            ->locale('fr-FR')
            ->build();

        $locale = $picker->pick($spec);

        $this->assertSame('fr-FR', $locale);
    }

    public function test_locale_picker_uses_catalog_when_no_spec_locale(): void
    {
        $picker = new LocalePicker();
        $spec = new GenerationSpec();

        $locale = $picker->pick($spec, 12345);

        $this->assertNotEmpty($locale);
        $this->assertTrue(LocaleCatalog::isSupported($locale));
    }

    public function test_locale_picker_is_deterministic_with_seed(): void
    {
        $picker = new LocalePicker();
        $spec = new GenerationSpec();

        $locale1 = $picker->pick($spec, 88888);
        $locale2 = $picker->pick($spec, 88888);

        $this->assertSame($locale1, $locale2);
    }

    // ========== ARCH PICKER TESTS ==========

    public function test_arch_picker_uses_spec_arch_when_provided(): void
    {
        $picker = new ArchPicker();
        $spec = GenerationSpec::create()
            ->arch('ARM64')
            ->build();

        $arch = $picker->pick(DeviceType::Mobile, $spec);

        $this->assertSame('ARM64', $arch);
    }

    public function test_arch_picker_uses_mobile_catalog_for_mobile_device(): void
    {
        $picker = new ArchPicker();
        $spec = new GenerationSpec();

        $arch = $picker->pick(DeviceType::Mobile, $spec, 12345);

        $this->assertNotEmpty($arch);
        $this->assertTrue(ArchCatalog::isSupported($arch));
        // Should be ARM or ARM64 for mobile
        $this->assertContains($arch, ['ARM', 'ARM64']);
    }

    public function test_arch_picker_uses_mobile_catalog_for_tablet_device(): void
    {
        $picker = new ArchPicker();
        $spec = new GenerationSpec();

        $arch = $picker->pick(DeviceType::Tablet, $spec, 12345);

        $this->assertNotEmpty($arch);
        $this->assertContains($arch, ['ARM', 'ARM64']);
    }

    public function test_arch_picker_uses_desktop_catalog_for_desktop_device(): void
    {
        $picker = new ArchPicker();
        $spec = new GenerationSpec();

        $arch = $picker->pick(DeviceType::Desktop, $spec, 12345);

        $this->assertNotEmpty($arch);
        $this->assertTrue(ArchCatalog::isSupported($arch));
    }

    public function test_arch_picker_is_deterministic_with_seed(): void
    {
        $picker = new ArchPicker();
        $spec = new GenerationSpec();

        $arch1 = $picker->pick(DeviceType::Desktop, $spec, 77777);
        $arch2 = $picker->pick(DeviceType::Desktop, $spec, 77777);

        $this->assertSame($arch1, $arch2);
    }
}
