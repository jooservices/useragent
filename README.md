# jooservices/useragent

[![CI](https://github.com/jooservices/useragent/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/jooservices/useragent/actions/workflows/ci.yml)
[![OpenSSF Scorecard](https://api.securityscorecards.dev/projects/github.com/jooservices/useragent/badge)](https://securityscorecards.dev/viewer/?uri=github.com/jooservices/useragent)
[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-blue.svg)](https://www.php.net/)
[![GitHub Release](https://img.shields.io/github/v/release/jooservices/useragent?display_name=tag)](https://github.com/jooservices/useragent/releases)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

PHP 8.5+ deterministic generator of coherent synthetic browser User-Agent profiles for tests, fixtures, and controlled client configuration.

## Features

- Checksummed bundled dataset with provenance on every result
- Seeded, weighted, uniform, and round-robin profile selection
- Immutable fluent request builder and bounded unique batch generation
- `generatePool()` for a list of ready-to-use User-Agent strings
- Human-readable profile labels and exact User-Agent fingerprints
- Non-interactive CLI with JSON and NDJSON output

## Requirements

- PHP `>= 8.5`
- `ext-json`
- `jooservices/dto ^3.0`

## Installation

```bash
composer require jooservices/useragent:^4.0
```

## Quick start

```php
use JOOservices\UserAgent\Generator;
use JOOservices\UserAgent\Domain\UniquePolicy;

$generator = Generator::create();
$pool = $generator->builder()
    ->chrome()
    ->windows()
    ->desktop()
    ->seed(42)
    ->generatePool(10, UniquePolicy::AllowDuplicates);
```

Use `UniquePolicy::Fail` when every generated string must be different. Unique capacity depends on the selected filters and bundled dataset; an exhausted request throws `UniqueGenerationExhausted`. `profile->identity()` is a readable profile label and can represent multiple User-Agent strings. Use `GenerationResult::fingerprint()` to key the exact rendered string.

## Design notes

- Reuse one `Generator` instance in long-running processes; it loads and validates the bundled dataset once.
- A User-Agent string is synthetic metadata, not browser emulation.
- `recent(6)` uses the bundled dataset's `effectiveAt` date, so seeded output remains reproducible.
- The package performs no network access and does not accept a caller-provided dataset path.

## Documentation

- [Changelog](CHANGELOG.md)
- [Upgrade guide](UPGRADE-4.0.md)
- [Contributing](CONTRIBUTING.md)
- [Workflows](WORKFLOWS.md)

## Development

```bash
make build
make install
make lint
make test
make ci
```

## Community

- [Security policy](SECURITY.md)
- [Support](SUPPORT.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Governance](GOVERNANCE.md)

## License

MIT — see [LICENSE](LICENSE).
