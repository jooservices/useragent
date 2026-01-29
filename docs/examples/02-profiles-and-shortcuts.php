<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use JOOservices\UserAgent\Service\Profiles\Profiles;
use JOOservices\UserAgent\Service\UserAgentService;

$service = new UserAgentService;
$profiles = new Profiles($service);

echo "=== Profiles & Shortcuts ===\n\n";

// 1. Desktop Chrome (Most common for scraping)
echo '1. Desktop Chrome (Windows): '.$profiles->desktopChrome->windows()."\n";
echo '2. Desktop Chrome (macOS):   '.$profiles->desktopChrome->macos()."\n";
echo '3. Desktop Chrome (Linux):   '.$profiles->desktopChrome->linux()."\n";
echo '4. Desktop Chrome (Any):     '.$profiles->desktopChrome->any()."\n\n";

// 2. Mobile Safari (Common for checking mobile views)
echo '5. Mobile Safari (iPhone):   '.$profiles->mobileSafari->iphone()."\n";
echo '6. Mobile Safari (iPad):     '.$profiles->mobileSafari->ipad()."\n\n";

// 3. Android Chrome
echo '7. Android Chrome (Phone):   '.$profiles->androidChrome->phone()."\n";
echo '8. Android Chrome (Tablet):  '.$profiles->androidChrome->tablet()."\n\n";

// 4. Broad Categories (Random browser within category)
echo '9. Any Random Mobile UA:     '.$profiles->randomMobile()."\n";
echo '10. Any Random Desktop UA:   '.$profiles->randomDesktop()."\n";
