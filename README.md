# UserAgent Library

A powerful, comprehensive PHP library for generating realistic, specification-compliant User-Agent strings.

## Features

- **Realistic Generation**: Generates authentic User-Agents for Chrome, Firefox, Safari, and Edge.
- **Specification System**: Fluent builder API to define exact requirements (device, OS, version, etc.).
- **Smart Pickers**: Intelligent selection of versions, models, locales, and architectures based on platform validity.
- **Strategies**: Multiple selection strategies (Uniform, Weighted Market Share, Round Robin, Avoid Recent).
- **Profiles**: Pre-configured shortcuts for common profiles (Desktop Chrome, Mobile Safari, etc.).
- **Deterministic**: Seed-based generation for reproducible testing.
- **High Quality**: 100% Type-safe, PHPStan Level 9, 99% Test Coverage.

## Documentation

- **[Usage Guide](docs/usage.md)**: Full documentation on generation, strategies, and shortcuts.
- **[Examples](docs/examples/)**: Runnable scripts demonstrating all features.
- **[Contributing](CONTRIBUTING.md)**: Guide for contributors.
- **[Security](SECURITY.md)**: Vulnerability reporting policy.

## Installation

```bash
composer require jooservices/useragent
```

## CLI Usage

The library includes a zero-dependency CLI tool for generating strings from the command line.

```bash
# Generate 1 random string
./vendor/bin/useragent

# Generate 5 strings
./vendor/bin/useragent --count=5

# Specific constraints
./vendor/bin/useragent --browser=firefox --os=windows
./vendor/bin/useragent --device=mobile --browser=safari
```

## Quick Start

### Basic Usage

Generate a random, realistic User-Agent string using weighted market share probabilities:

```php
use JOOservices\UserAgent\Service\UserAgentService;

$service = new UserAgentService();
$ua = $service->generate();

echo $ua;
// Output option: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
```

### Using Profiles (Shortcuts)

Use pre-defined profiles for common scenarios:

```php
use JOOservices\UserAgent\Service\Profiles\Profiles;

$profiles = new Profiles($service);

// Desktop Chrome on Windows
echo $profiles->desktopChrome->windows();

// iPhone Safari
echo $profiles->mobileSafari->iphone();

// Android Chrome
echo $profiles->androidChrome->phone();

// Random Mobile Device
echo $profiles->randomMobile();
```

### Advanced Specification

Use the builder API for precise control:

```php
use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Spec\GenerationSpec;

$spec = GenerationSpec::create()
    ->browser(BrowserFamily::Chrome)
    ->device(DeviceType::Mobile)
    ->os(OperatingSystem::Android)
    ->versionMin(100)
    ->locale('fr-FR')
    ->build();

$ua = $service->generate($spec);
```

### Deterministic Generation (For Tests)

Pass a seed to generate the exact same UA every time:

```php
$seed = 12345;
$ua1 = $service->generate($spec, seed: $seed);
$ua2 = $service->generate($spec, seed: $seed);

assert($ua1 === $ua2); // True
```

## Architecture

- **Service Layer**: `UserAgentService` orchestrates the generation process.
- **Pickers**: specialized logic for selecting `Version`, `Model`, `Locale`, and `Arch`.
- **Templates**: Browser-specific templates (Chrome, Firefox, Safari, Edge) with device/OS awareness.
- **Filters**: System to filter valid templates based on constraints.
- **History**: `LruHistory` prevents repeating recently generated UAs.

## Requirements

- PHP 8.2+
- `random_int()` support

## License

MIT
