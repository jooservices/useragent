<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Spec\RandomSpec;

$service = new UserAgentService;

echo "=== Deterministic Generation & History ===\n\n";

// 1. Deterministic Generation (Seeding)
// By passing the same seed, you get exactly the same sequence of User-Agents.
// This is critical for reproducible tests.

echo "1. Seeding with 999:\n";
$seed = 999;

$ua1 = $service->generate(seed: $seed);
$ua2 = $service->generate(seed: $seed);

echo "   UA 1: $ua1\n";
echo "   UA 2: $ua2\n";

if ($ua1 === $ua2) {
    echo "   [SUCCESS] Both UAs are identical.\n\n";
} else {
    echo "   [FAILURE] UAs differ!\n\n";
}

// 2. RandomSpec Configuration
// You can control the random behavior via RandomSpec embedded in GenerationSpec.

echo "2. Using RandomSpec in GenerationSpec:\n";

$specWithSeed = GenerationSpec::create()
    ->randomSpec(new RandomSpec(
        seed: 555,
        historyWindow: 5, // Remember last 5 UAs
        retryBudget: 10   // Try 10 times to find a fresh UA
    ))
    ->build();

$ua3 = $service->generate($specWithSeed);
$ua4 = $service->generate($specWithSeed); // Same spec = Same seed = Same result

echo "   UA 3: $ua3\n";
echo "   UA 4: $ua4\n";
echo "   (Note: They are identical because the seed is baked into the spec)\n\n";

// 3. Avoiding Repetition
// The Service automatically maintains an LRU (Least Recently Used) history
// to avoid returning the same UA consecutively if possible, but ONLY if
// the seed changes or is not provided.
// If you use a fixed seed, you force the RNG to be in the same state, so you get the same result.

echo "3. Consecutive Random selection (No Seed):\n";
$ua5 = $service->generate();
$ua6 = $service->generate();

echo "   UA 5: $ua5\n";
echo "   UA 6: $ua6\n";

if ($ua5 !== $ua6) {
    echo "   [info] UAs are different (as expected).\n";
}
