# Usage Guide

This guide covers how to use the UserAgent library to generate realistic User-Agent strings.

## Basic Generation

The simplest way to use the library is with the `UserAgentService`.

```php
use JOOservices\UserAgent\Service\UserAgentService;

$service = new UserAgentService();

// Generate a random, realistic User-Agent
$ua = $service->generate();

echo $ua;
// Example: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36
// Example: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36
```

## Static Facade API (Recommended)

The `UserAgent` facade provides a fluent, static interface for generating strings. This is often more convenient than instantiating the service directly.

### Usage
```php
use JOOservices\UserAgent\UserAgent;

// Simple
echo UserAgent::generate();

// Chaining Constraints
echo UserAgent::firefox()->windows()->generate();
echo UserAgent::safari()->mobile()->generate();
```

### Unique Generation
Guarantees uniqueness within the current request lifecycle. Useful for scraping loops or seeding databases.

```php
for ($i = 0; $i < 50; $i++) {
    // Each UA is guaranteed to be distinct from the previous ones
    echo UserAgent::unique()->generate();
}

// Reset the uniqueness history if needed
UserAgent::resetUnique();
```

### Exclusion Mode
Inverts the selection logic.

```php
// Generate any UA that is NOT Chrome
echo UserAgent::exclude()->chrome()->generate();

// Generate any UA that is NOT Mobile
echo UserAgent::exclude()->mobile()->generate();
```


## Using Profiles (Shortcuts)

For common scenarios, use the pre-configured profiles accessible via the `Profiles` class.

```php
use JOOservices\UserAgent\Service\Profiles\Profiles;

$profiles = new Profiles($service);

// Desktop Chrome on different OSs
echo $profiles->desktopChrome->windows();
echo $profiles->desktopChrome->macos();
echo $profiles->desktopChrome->linux();

// Mobile Devices
echo $profiles->mobileSafari->iphone();
echo $profiles->mobileSafari->ipad();
echo $profiles->androidChrome->phone();

// Random by Category
echo $profiles->randomDesktop();
echo $profiles->randomMobile();
```

## Precise Control with GenerationSpec

For specific requirements, use the `GenerationSpec` builder.

```php
use JOOservices\UserAgent\Domain\Enums\{BrowserFamily, DeviceType, OperatingSystem};
use JOOservices\UserAgent\Spec\GenerationSpec;

// Create a specification
$spec = GenerationSpec::create()
    ->browser(BrowserFamily::Chrome)
    ->device(DeviceType::Desktop)
    ->os(OperatingSystem::MacOS)
    ->versionMin(110)
    // Optional: specific locale or architecture
    ->locale('fr-FR')
    ->arch('ARM64')
    ->build();

// Generate matching UA
$ua = $service->generate($spec);
```

### Supported Constraints
- `browser(BrowserFamily $family)`
- `device(DeviceType $type)`
- `os(OperatingSystem $os)`
- `versionMin(int $major)`
- `versionMax(int $major)`
- `versionExact(int $major)`
- `locale(string $locale)`
- `arch(string $arch)`

## Strategies

The library supports different strategies for selecting browsers when multiple options are available.

```php
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Strategies\{UniformStrategy, WeightedStrategy};

// 1. Weighted (Default) - Based on market share
$service->generate(GenerationSpec::create()->build());

// 2. Uniform - Equal probability for all browsers
$spec = GenerationSpec::create()
    ->strategy(UniformStrategy::class)
    ->build();
$service->generate($spec);
```

## Deterministic Generation

For testing, you can ensure the same input produces the same output by providing a seed.

```php
$seed = 12345;

$ua1 = $service->generate(null, seed: $seed);
$ua2 = $service->generate(null, seed: $seed);

assert($ua1 === $ua2); // Always true
```

## Advanced: History Tracking

To avoid repeating the same User-Agent in a short period, the service uses an LRU (Least Recently Used) history mechanism automatically.

By default, it remembers the last 100 generated UAs and attempts to pick a fresh one. You can configure this via `RandomSpec` if needed.

## CLI Tool

For quick tests or generating strings without writing PHP code, you can use the included CLI tool:

```bash
./vendor/bin/useragent --count=5 --browser=firefox
```

See the [README](../README.md#cli-usage) for full CLI documentation.
