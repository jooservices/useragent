<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Templates\Catalogs;

use JOOservices\UserAgent\Templates\Catalogs\ArchCatalog;
use JOOservices\UserAgent\Templates\Catalogs\LocaleCatalog;
use JOOservices\UserAgent\Templates\Catalogs\ModelCatalog;
use PHPUnit\Framework\TestCase;

/**
 * Consolidated tests for all catalog classes.
 */
final class CatalogsTest extends TestCase
{
    // ========== LOCALE CATALOG TESTS ==========

    public function test_locale_catalog_returns_locales(): void
    {
        $locales = LocaleCatalog::getLocales();

        $this->assertIsArray($locales);
        $this->assertNotEmpty($locales);
        $this->assertArrayHasKey('en-US', $locales);
        $this->assertArrayHasKey('zh-CN', $locales);
        $this->assertArrayHasKey('fr-FR', $locales);
    }

    public function test_locale_weights_sum_to_one(): void
    {
        $locales = LocaleCatalog::getLocales();
        $sum = array_sum($locales);

        $this->assertEqualsWithDelta(1.0, $sum, 0.01);
    }

    public function test_locale_catalog_returns_random_locale(): void
    {
        $locale = LocaleCatalog::getRandomLocale();

        $this->assertIsString($locale);
        $this->assertNotEmpty($locale);
        $this->assertTrue(LocaleCatalog::isSupported($locale));
    }

    public function test_locale_catalog_returns_deterministic_locale_with_seed(): void
    {
        $locale1 = LocaleCatalog::getRandomLocale(12345);
        $locale2 = LocaleCatalog::getRandomLocale(12345);

        $this->assertSame($locale1, $locale2);
    }

    public function test_locale_catalog_is_supported(): void
    {
        $this->assertTrue(LocaleCatalog::isSupported('en-US'));
        $this->assertTrue(LocaleCatalog::isSupported('zh-CN'));
        $this->assertFalse(LocaleCatalog::isSupported('xx-XX'));
        $this->assertFalse(LocaleCatalog::isSupported('invalid'));
    }

    // ========== MODEL CATALOG TESTS ==========

    public function test_model_catalog_returns_android_models(): void
    {
        $models = ModelCatalog::getAndroidModels();

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertArrayHasKey('SM-G998B', $models);
        $this->assertArrayHasKey('Pixel 7 Pro', $models);
    }

    public function test_model_catalog_returns_ios_models(): void
    {
        $models = ModelCatalog::getIosModels();

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertArrayHasKey('iPhone15,3', $models);
        $this->assertArrayHasKey('iPhone14,2', $models);
    }

    public function test_android_model_weights_sum_to_one(): void
    {
        $models = ModelCatalog::getAndroidModels();
        $sum = array_sum($models);

        $this->assertEqualsWithDelta(1.0, $sum, 0.01);
    }

    public function test_ios_model_weights_sum_to_one(): void
    {
        $models = ModelCatalog::getIosModels();
        $sum = array_sum($models);

        $this->assertEqualsWithDelta(1.0, $sum, 0.01);
    }

    public function test_model_catalog_returns_random_android_model(): void
    {
        $model = ModelCatalog::getRandomAndroidModel();

        $this->assertIsString($model);
        $this->assertNotEmpty($model);
        $this->assertArrayHasKey($model, ModelCatalog::getAndroidModels());
    }

    public function test_model_catalog_returns_random_ios_model(): void
    {
        $model = ModelCatalog::getRandomIosModel();

        $this->assertIsString($model);
        $this->assertNotEmpty($model);
        $this->assertArrayHasKey($model, ModelCatalog::getIosModels());
    }

    public function test_model_catalog_returns_deterministic_android_model_with_seed(): void
    {
        $model1 = ModelCatalog::getRandomAndroidModel(54321);
        $model2 = ModelCatalog::getRandomAndroidModel(54321);

        $this->assertSame($model1, $model2);
    }

    public function test_model_catalog_returns_deterministic_ios_model_with_seed(): void
    {
        $model1 = ModelCatalog::getRandomIosModel(98765);
        $model2 = ModelCatalog::getRandomIosModel(98765);

        $this->assertSame($model1, $model2);
    }

    // ========== ARCH CATALOG TESTS ==========

    public function test_arch_catalog_returns_architectures(): void
    {
        $architectures = ArchCatalog::getArchitectures();

        $this->assertIsArray($architectures);
        $this->assertNotEmpty($architectures);
        $this->assertArrayHasKey('x86_64', $architectures);
        $this->assertArrayHasKey('ARM64', $architectures);
        $this->assertArrayHasKey('x64', $architectures);
    }

    public function test_arch_catalog_returns_desktop_architectures(): void
    {
        $architectures = ArchCatalog::getDesktopArchitectures();

        $this->assertIsArray($architectures);
        $this->assertArrayHasKey('x86_64', $architectures);
        $this->assertArrayHasKey('ARM64', $architectures);
        $this->assertArrayNotHasKey('ARM', $architectures); // 32-bit ARM not common on desktop
    }

    public function test_arch_catalog_returns_mobile_architectures(): void
    {
        $architectures = ArchCatalog::getMobileArchitectures();

        $this->assertIsArray($architectures);
        $this->assertCount(2, $architectures);
        $this->assertArrayHasKey('ARM64', $architectures);
        $this->assertArrayHasKey('ARM', $architectures);
    }

    public function test_arch_weights_sum_to_one(): void
    {
        $architectures = ArchCatalog::getArchitectures();
        $sum = array_sum($architectures);

        $this->assertEqualsWithDelta(1.0, $sum, 0.01);
    }

    public function test_desktop_arch_weights_sum_to_one(): void
    {
        $architectures = ArchCatalog::getDesktopArchitectures();
        $sum = array_sum($architectures);

        $this->assertEqualsWithDelta(1.0, $sum, 0.01);
    }

    public function test_mobile_arch_weights_sum_to_one(): void
    {
        $architectures = ArchCatalog::getMobileArchitectures();
        $sum = array_sum($architectures);

        $this->assertEqualsWithDelta(1.0, $sum, 0.01);
    }

    public function test_arch_catalog_returns_random_desktop_architecture(): void
    {
        $arch = ArchCatalog::getRandomArchitecture(null, false);

        $this->assertIsString($arch);
        $this->assertNotEmpty($arch);
        $this->assertArrayHasKey($arch, ArchCatalog::getDesktopArchitectures());
    }

    public function test_arch_catalog_returns_random_mobile_architecture(): void
    {
        $arch = ArchCatalog::getRandomArchitecture(null, true);

        $this->assertIsString($arch);
        $this->assertNotEmpty($arch);
        $this->assertArrayHasKey($arch, ArchCatalog::getMobileArchitectures());
    }

    public function test_arch_catalog_returns_deterministic_architecture_with_seed(): void
    {
        $arch1 = ArchCatalog::getRandomArchitecture(11111, false);
        $arch2 = ArchCatalog::getRandomArchitecture(11111, false);

        $this->assertSame($arch1, $arch2);
    }

    public function test_arch_catalog_is_supported(): void
    {
        $this->assertTrue(ArchCatalog::isSupported('x86_64'));
        $this->assertTrue(ArchCatalog::isSupported('ARM64'));
        $this->assertFalse(ArchCatalog::isSupported('invalid'));
        $this->assertFalse(ArchCatalog::isSupported('PowerPC'));
    }

    public function test_locale_catalog_fallback_to_en_us(): void
    {
        // Test with seed that would exceed cumulative weights
        $locale = LocaleCatalog::getRandomLocale(999999);
        $this->assertNotEmpty($locale);
        $this->assertTrue(LocaleCatalog::isSupported($locale));
    }

    public function test_model_catalog_fallback_to_first_item(): void
    {
        // Test with seed that would exceed cumulative weights
        $androidModel = ModelCatalog::getRandomAndroidModel(999999);
        $this->assertNotEmpty($androidModel);

        $iosModel = ModelCatalog::getRandomIosModel(999999);
        $this->assertNotEmpty($iosModel);
    }

    public function test_arch_catalog_fallback_returns(): void
    {
        // Test with seed that would exceed cumulative weights
        $desktopArch = ArchCatalog::getRandomArchitecture(999999, false);
        $this->assertNotEmpty($desktopArch);

        $mobileArch = ArchCatalog::getRandomArchitecture(999999, true);
        $this->assertNotEmpty($mobileArch);
    }
}
