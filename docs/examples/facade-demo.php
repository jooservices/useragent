<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use JOOservices\UserAgent\UserAgent;

echo "=== UserAgent Facade Demo ===\n\n";

// 1. Basic Generation
echo "1. Random UA:\n";
echo '   '.UserAgent::generate()."\n\n";

// 2. Specific Constraints
echo "2. Chrome on Windows:\n";
echo '   '.UserAgent::chrome()->windows()->generate()."\n\n";

echo "3. Safari on Mobile:\n";
echo '   '.UserAgent::safari()->mobile()->generate()."\n\n";

echo "4. Firefox on Linux:\n";
echo '   '.UserAgent::firefox()->linux()->generate()."\n\n";

// 3. Exclusion
echo "5. Anything BUT Mobile:\n";
echo '   '.UserAgent::exclude()->mobile()->generate()."\n\n";

// 4. Unique Generation
echo "6. generating 5 UNIQUE User-Agents:\n";
for ($i = 0; $i < 5; $i++) {
    echo '   '.UserAgent::unique()->generate()."\n";
}
echo "\n";

// 5. Handling Edge Cases (Linux only has Chrome/Firefox usually)
echo "7. Linux UA (Auto-validating browser choice):\n";
echo '   '.UserAgent::linux()->generate()."\n";
