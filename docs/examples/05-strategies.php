<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Strategies\AvoidRecentStrategy;
use JOOservices\UserAgent\Strategies\RoundRobinStrategy;
use JOOservices\UserAgent\Strategies\UniformStrategy;

$service = new UserAgentService;

echo "=== Selection Strategies ===\n\n";

// 1. Weighted Strategy (Default)
// Biased towards popular browsers (Chrome) based on market share.
echo '1. Weighted (Default): '.$service->generate()."\n\n";

// 2. Uniform Strategy
// Equal probability for ALL available browsers (Chrome, Firefox, Safari, Edge have equal chance).
$uniformSpec = GenerationSpec::create()
    ->strategy(UniformStrategy::class)
    ->build();

echo '2. Uniform:            '.$service->generate($uniformSpec)."\n\n";

// 3. Avoid Recent Strategy
// Tries to pick a browser/template that hasn't been used recently.
$freshSpec = GenerationSpec::create()
    ->strategy(AvoidRecentStrategy::class)
    ->build();

echo '3. Avoid Recent:       '.$service->generate($freshSpec)."\n";
echo '4. Avoid Recent (2):   '.$service->generate($freshSpec)."\n\n";

// 4. Round Robin Strategy
// Cycles through browsers sequentially: Chrome -> Firefox -> Safari -> Edge -> Chrome...
$rrSpec = GenerationSpec::create()
    ->strategy(RoundRobinStrategy::class)
    ->build();

echo '5. Round Robin (1):    '.$service->generate($rrSpec)."\n";
echo '6. Round Robin (2):    '.$service->generate($rrSpec)."\n";
echo '7. Round Robin (3):    '.$service->generate($rrSpec)."\n";
echo '8. Round Robin (4):    '.$service->generate($rrSpec)."\n";
