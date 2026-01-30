<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Facade;

use Exception;
use JOOservices\UserAgent\Domain\Enums\BotType;
use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Templates\Bots\BotTemplate;
use RuntimeException;

final class UserAgentBuilder
{
    private bool $unique = false;

    private bool $excluding = false;

    private int $retryLimit = 50;

    private ?BotType $botType = null;

    private bool $botMobile = false;

    private ?int $maxAgeMonths = null;

    private const SUPPORTED_BROWSERS = [
        BrowserFamily::Chrome,
        BrowserFamily::Firefox,
        BrowserFamily::Safari,
        BrowserFamily::Edge,
    ];

    private const SUPPORTED_DEVICES = [
        DeviceType::Desktop,
        DeviceType::Mobile,
        DeviceType::Tablet,
    ];

    private const SUPPORTED_OSES = [
        OperatingSystem::Windows,
        OperatingSystem::MacOS,
        OperatingSystem::Linux,
        OperatingSystem::Android,
        OperatingSystem::iOS,
    ];

    /** @var array<string, BrowserFamily> */
    private array $allowedBrowsers = [];

    /** @var array<string, DeviceType> */
    private array $allowedDevices = [];

    /** @var array<string, OperatingSystem> */
    private array $allowedOses = [];

    public function __construct(
        private readonly UserAgentService $service
    ) {
        // Start with supported options
        foreach (self::SUPPORTED_BROWSERS as $browser) {
            $this->allowedBrowsers[$browser->value] = $browser;
        }
        foreach (self::SUPPORTED_DEVICES as $device) {
            $this->allowedDevices[$device->value] = $device;
        }
        foreach (self::SUPPORTED_OSES as $os) {
            $this->allowedOses[$os->value] = $os;
        }
    }

    public function unique(): self
    {
        $this->unique = true;

        return $this;
    }

    public function exclude(): self
    {
        $this->excluding = true;

        return $this;
    }

    // --- Browsers ---

    public function chrome(): self
    {
        return $this->handleBrowser(BrowserFamily::Chrome);
    }

    public function firefox(): self
    {
        return $this->handleBrowser(BrowserFamily::Firefox);
    }

    public function safari(): self
    {
        return $this->handleBrowser(BrowserFamily::Safari);
    }

    public function edge(): self
    {
        return $this->handleBrowser(BrowserFamily::Edge);
    }

    private function handleBrowser(BrowserFamily $browser): self
    {
        if ($this->excluding) {
            unset($this->allowedBrowsers[$browser->value]);
        } else {
            // Include mode
            if (count($this->allowedBrowsers) === count(self::SUPPORTED_BROWSERS)) {
                $this->allowedBrowsers = [];
            }
            $this->allowedBrowsers[$browser->value] = $browser;
        }

        return $this;
    }

    // --- Devices ---

    public function desktop(): self
    {
        return $this->handleDevice(DeviceType::Desktop);
    }

    public function mobile(): self
    {
        return $this->handleDevice(DeviceType::Mobile);
    }

    public function tablet(): self
    {
        return $this->handleDevice(DeviceType::Tablet);
    }

    private function handleDevice(DeviceType $device): self
    {
        if ($this->excluding) {
            unset($this->allowedDevices[$device->value]);
        } else {
            if (count($this->allowedDevices) === count(self::SUPPORTED_DEVICES)) {
                $this->allowedDevices = [];
            }
            $this->allowedDevices[$device->value] = $device;
        }

        return $this;
    }

    // --- OS ---

    public function windows(): self
    {
        return $this->handleOs(OperatingSystem::Windows);
    }

    public function macos(): self
    {
        return $this->handleOs(OperatingSystem::MacOS);
    }

    public function linux(): self
    {
        return $this->handleOs(OperatingSystem::Linux);
    }

    public function android(): self
    {
        return $this->handleOs(OperatingSystem::Android);
    }

    public function ios(): self
    {
        return $this->handleOs(OperatingSystem::iOS);
    }

    private function handleOs(OperatingSystem $os): self
    {
        if ($this->excluding) {
            unset($this->allowedOses[$os->value]);
        } else {
            if (count($this->allowedOses) === count(self::SUPPORTED_OSES)) {
                $this->allowedOses = [];
            }
            $this->allowedOses[$os->value] = $os;
        }

        return $this;
    }

    // --- Bots ---

    /**
     * Generate a Googlebot User-Agent.
     */
    public function googlebot(): string
    {
        return $this->bot(BotType::Googlebot);
    }

    /**
     * Generate a Bingbot User-Agent.
     */
    public function bingbot(): string
    {
        return $this->bot(BotType::Bingbot);
    }

    /**
     * Generate a bot User-Agent for the specified type.
     */
    public function bot(BotType $type): string
    {
        $template = new BotTemplate;

        return $template->generate($type, $this->botMobile);
    }

    /**
     * Set mobile mode for bot generation.
     */
    public function botAsMobile(): self
    {
        $this->botMobile = true;

        return $this;
    }

    // --- Age Control ---

    /**
     * Only generate UAs from recent browser versions.
     */
    public function recent(int $months = 6): self
    {
        $this->maxAgeMonths = $months;

        return $this;
    }

    // --- Batch Generation ---

    /**
     * Generate multiple User-Agent strings with unique guarantee.
     *
     * @return array<string>
     */
    public function generateMany(int $count): array
    {
        $results = [];
        $wasUnique = $this->unique;
        $this->unique = true; // Force unique mode for batch

        try {
            for ($i = 0; $i < $count; $i++) {
                $results[] = $this->generate();
            }
        } finally {
            $this->unique = $wasUnique;
        }

        return $results;
    }

    // --- Generation ---

    public function generate(): string
    {
        // Handle bot generation
        if ($this->botType !== null) {
            return $this->bot($this->botType);
        }

        // 1. Resolve final constraints
        $spec = $this->resolveSpec();

        // 2. Generation Loop (for uniqueness)
        $attempts = 0;
        do {
            try {
                $ua = $this->service->generate($spec);
            } catch (Exception $e) {
                // For debugging only
                echo 'Failed Spec: Browser='.($spec->browser?->value ?? 'null')
                     .', Device='.($spec->device?->value ?? 'null')
                     .', OS='.($spec->os?->value ?? 'null')."\n";

                throw $e;
            }

            if (! $this->unique) {
                return $ua;
            }

            if (UniqueGuard::check($ua)) {
                UniqueGuard::add($ua);

                return $ua;
            }

            $attempts++;
        } while ($attempts < $this->retryLimit);

        throw new RuntimeException("Failed to generate unique User-Agent after {$this->retryLimit} attempts.");
    }

    private function resolveSpec(): GenerationSpec
    {
        $builder = GenerationSpec::create();

        // 1. Pick Browser first (Foundation)
        if (empty($this->allowedBrowsers)) {
            throw new RuntimeException('No browsers left in candidate pool (all excluded?)');
        }

        // --- NEW: Backward Filter Browsers by Allowed OSs ---
        // If the user said ->linux(), we must NOT pick Safari, because Safari has NO path to Linux.
        // We filter validBrowsers to those that have at least one valid path to an allowed OS.
        $validBrowsers = $this->filterBrowsersByAllowedOses($this->allowedBrowsers, $this->allowedOses);

        if (empty($validBrowsers)) {
            throw new RuntimeException('No valid Browser found compatible with allowed OSs.');
        }

        /** @var BrowserFamily $browser */
        $browser = $this->pickRandom($validBrowsers);
        $builder->browser($browser);

        // 2. Filter Devices supported by this Browser
        $validDevices = $this->filterDevicesByBrowser($browser);

        // Refinement: Filter Devices that can support at least ONE of the allowed OSs
        // If user said ->windows(), allowedOSs=[Windows].
        // We should NOT pick Tablet, because Tablet supports [Android, iOS], which has NO overlap with [Windows].
        $validDevices = $this->filterDevicesBySupportedOses($validDevices, $this->allowedOses);

        // Intersect valid Devices with allowed Devices
        $finalDevices = array_intersect_key($validDevices, $this->allowedDevices);

        if (empty($finalDevices)) {
            throw new RuntimeException("No valid Device found for Browser: {$browser->value} compatible with allowed OSs.");
        }
        /** @var DeviceType $device */
        $device = $this->pickRandom($finalDevices);
        $builder->device($device);

        // 3. Filter OS candidates based on Device AND Browser
        $validOses = $this->filterOsesByDevice($device, $browser);

        // Intersect valid OSs with allowed OSs
        $finalOses = array_intersect_key($validOses, $this->allowedOses);

        if (empty($finalOses)) {
            throw new RuntimeException("No valid OS found for Device: {$device->value} with current exclusions.");
        }

        /** @var OperatingSystem $os */
        $os = $this->pickRandom($finalOses);
        $builder->os($os);

        return $builder->build();
    }

    /**
     * @return array<string, DeviceType>
     */
    private function filterDevicesByBrowser(BrowserFamily $browser): array
    {
        // Define browser capabilities here.
        // This should ideally map to the Templates, but hardcoding for the Builder is acceptable for now.
        $map = match ($browser) {
            BrowserFamily::Chrome, BrowserFamily::Firefox, BrowserFamily::Edge => [
                DeviceType::Desktop,
                DeviceType::Mobile,
                DeviceType::Tablet,
            ],
            BrowserFamily::Safari => [
                DeviceType::Desktop,
                DeviceType::Mobile,
                DeviceType::Tablet,
            ],
            default => [
                DeviceType::Desktop,
            ],
        };

        // Note: Safari on Windows/Linux is technically possible in old versions but this lib likely targets modern.
        // The Library's SafariTemplate supports: Desktop (MacOS), Mobile (iOS).
        // So Safari + Desktop is OK (but OS must be Mac).
        // Safari + Mobile is OK (but OS must be iOS).

        $result = [];
        foreach ($map as $device) {
            $result[$device->value] = $device;
        }

        return $result;
    }

    /**
     * @return array<string, OperatingSystem>
     */
    private function filterOsesByDevice(DeviceType $device, BrowserFamily $browser): array
    {
        $map = match ($device) {
            DeviceType::Desktop => match ($browser) {
                BrowserFamily::Safari => [OperatingSystem::MacOS],
                BrowserFamily::Edge => [OperatingSystem::Windows, OperatingSystem::MacOS],
                default => [
                    OperatingSystem::Windows,
                    OperatingSystem::MacOS,
                    OperatingSystem::Linux,
                    OperatingSystem::ChromeOS,
                ],
            },
            DeviceType::Mobile, DeviceType::Tablet => [
                OperatingSystem::Android,
                OperatingSystem::iOS,
            ],
            default => [
                OperatingSystem::Linux,
                OperatingSystem::Windows,
                OperatingSystem::MacOS,
            ],
        };

        // Further refinement for Mobile Safari (must be iOS)
        if (($device === DeviceType::Mobile || $device === DeviceType::Tablet) && $browser === BrowserFamily::Safari) {
            $map = [OperatingSystem::iOS];
        }

        $result = [];
        foreach ($map as $os) {
            $result[$os->value] = $os;
        }

        return $result;
    }

    /**
     * @param array<string, DeviceType>      $devices
     * @param array<string, OperatingSystem> $allowedOses
     *
     * @return array<string, DeviceType>
     */
    private function filterDevicesBySupportedOses(array $devices, array $allowedOses): array
    {
        $result = [];
        foreach ($devices as $key => $device) {
            // Get all OSs supported by this device
            $supportedOses = $this->getGenericSupportedOses($device);

            // Check if there is ANY overlap with allowedOses
            $intersect = array_intersect_key($supportedOses, $allowedOses);

            if (! empty($intersect)) {
                $result[$key] = $device;
            }
        }

        return $result;
    }

    /**
     * @return array<string, OperatingSystem>
     */
    private function getGenericSupportedOses(DeviceType $device): array
    {
        $list = match ($device) {
            DeviceType::Desktop => [
                OperatingSystem::Windows,
                OperatingSystem::MacOS,
                OperatingSystem::Linux,
                OperatingSystem::ChromeOS,
            ],
            DeviceType::Mobile, DeviceType::Tablet => [
                OperatingSystem::Android,
                OperatingSystem::iOS,
            ],
            default => [],
        };

        $map = [];
        foreach ($list as $os) {
            $map[$os->value] = $os;
        }

        return $map;
    }

    /**
     * @param array<mixed> $items
     */
    private function pickRandom(array $items): mixed
    {
        $key = array_rand($items);

        return $items[$key];
    }

    /**
     * @param array<string, BrowserFamily>   $browsers
     * @param array<string, OperatingSystem> $allowedOses
     *
     * @return array<string, BrowserFamily>
     */
    private function filterBrowsersByAllowedOses(array $browsers, array $allowedOses): array
    {
        // If allowedOses is technically "all supported OSes", we don't need to filter strict.
        if (count($allowedOses) === count(self::SUPPORTED_OSES)) {
            return $browsers;
        }

        $result = [];
        foreach ($browsers as $key => $browser) {
            if ($this->browserSupportsAnyOs($browser, $allowedOses)) {
                $result[$key] = $browser;
            }
        }

        return $result;
    }

    /**
     * @param array<string, OperatingSystem> $allowedOses
     */
    private function browserSupportsAnyOs(BrowserFamily $browser, array $allowedOses): bool
    {
        // Get all OSs this browser supports across ALL devices
        // This is a union of filterOsesByDevice(Device, Browser) for all Devices
        $supportedOses = [];
        foreach (self::SUPPORTED_DEVICES as $device) {
            $oses = $this->filterOsesByDevice($device, $browser);
            foreach ($oses as $os) {
                $supportedOses[$os->value] = $os;
            }
        }

        // Check overlap
        return ! empty(array_intersect_key($supportedOses, $allowedOses));
    }
}
