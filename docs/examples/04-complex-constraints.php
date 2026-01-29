<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;

$service = new UserAgentService;

echo "=== Complex Constraints ===\n\n";

// 1. Precise Environment: Desktop Chrome on MacOS with min version 120
$spec1 = GenerationSpec::create()
    ->browser(BrowserFamily::Chrome)
    ->device(DeviceType::Desktop)
    ->os(OperatingSystem::MacOS)
    ->versionMin(120)
    ->build();

echo '1. Recent Mac Chrome: '.$service->generate($spec1)."\n\n";

// 2. Mobile Locale: Android Chrome in French
$spec2 = GenerationSpec::create()
    ->browser(BrowserFamily::Chrome)
    ->device(DeviceType::Mobile)
    ->os(OperatingSystem::Android)
    ->locale('fr-FR')
    ->build();

echo '2. French Android:    '.$service->generate($spec2)."\n\n";

// 3. Old Browser: Chrome version 100-110
$spec3 = GenerationSpec::create()
    ->browser(BrowserFamily::Chrome)
    ->versionMin(100)
    ->versionMax(110)
    ->build();

echo '3. Older Chrome:      '.$service->generate($spec3)."\n\n";

// 4. Specific Architecture: Linux on ARM64
$spec4 = GenerationSpec::create()
    ->os(OperatingSystem::Linux)
    ->arch('aarch64')
    ->build();

echo '4. ARM64 Linux:       '.$service->generate($spec4)."\n";
