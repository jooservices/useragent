<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use JOOservices\UserAgent\Service\UserAgentService;

$service = new UserAgentService;

echo "=== Basic User-Agent Generation ===\n\n";

// 1. Generate a completely random User-Agent (weighted by market share)
$ua = $service->generate();
echo "1. Random UA (Weighted): $ua\n";

// 2. Generate another one (likely different)
$ua2 = $service->generate();
echo "2. Another Random UA:    $ua2\n";

// 3. Generate a deterministic UA using a seed
// This will ALWAYS return the same string for the same seed.
$seed = 12345;
$ua3 = $service->generate(seed: $seed);
echo "3. Deterministic UA:     $ua3\n";
