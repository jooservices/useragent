<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Exceptions\InvalidSpecException;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;

$service = new UserAgentService;

echo "=== Error Handling ===\n\n";

// 1. Impossible Combination
// Trying to generate Safari on Windows (which doesn't exist in modern versions)
echo "1. Testing Impossible Constraint (Safari on Windows)...\n";

try {
    $impossibleSpec = GenerationSpec::create()
        ->browser(BrowserFamily::Safari)
        ->os(OperatingSystem::Windows)
        ->build();

    $service->generate($impossibleSpec);

} catch (InvalidSpecException $e) {
    echo '   [Caught InvalidSpecException]: '.$e->getMessage()."\n\n";
} catch (JOOservices\UserAgent\Exceptions\NoCandidateException $e) {
    echo '   [Caught NoCandidateException]: '.$e->getMessage()."\n\n";
}

// 2. Invalid Version Range
// Min version > Max version
echo "2. Testing Invalid Version Range (Min 200 > Max 100)...\n";

try {
    $invalidRangeSpec = GenerationSpec::create()
        ->versionMin(200)
        ->versionMax(100)
        ->build();

    $service->generate($invalidRangeSpec);

} catch (InvalidSpecException $e) {
    echo '   [Caught InvalidSpecException]: '.$e->getMessage()."\n\n";
}

// 3. Fallback Behavior
// When no browser matches a specific filter, it throws exception.
// Note: If you want soft fail, currently the library enforces strict constraints.
