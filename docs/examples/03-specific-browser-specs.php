<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;

$service = new UserAgentService;

echo "=== Specific Browser Specs ===\n\n";

// 1. Force Chrome
$chromeSpec = GenerationSpec::create()
    ->browser(BrowserFamily::Chrome)
    ->build();
echo '1. Chrome:  '.$service->generate($chromeSpec)."\n";

// 2. Force Firefox
$firefoxSpec = GenerationSpec::create()
    ->browser(BrowserFamily::Firefox)
    ->build();
echo '2. Firefox: '.$service->generate($firefoxSpec)."\n";

// 3. Force Safari
$safariSpec = GenerationSpec::create()
    ->browser(BrowserFamily::Safari)
    ->build();
echo '3. Safari:  '.$service->generate($safariSpec)."\n";

// 4. Force Edge
$edgeSpec = GenerationSpec::create()
    ->browser(BrowserFamily::Edge)
    ->build();
echo '4. Edge:    '.$service->generate($edgeSpec)."\n";
