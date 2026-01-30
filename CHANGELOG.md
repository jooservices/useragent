# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-01-30

### Improved
- **Code Quality**: Enforced strict linting standards with `Laravel Pint`, `PHP_CodeSniffer`, `PHPStan` (Level 9), and `PHPMD`.
- **Developer Experience**: Consolidated linting commands in `composer.json` (`lint`, `fix`, `check`).
- **Documentation**: Updated PHPDoc annotations for better IDE support and static analysis.

### Fixed
- Resolved unused parameter warnings in `ModelPicker`, `UserAgentService`, and Browser Templates.
- Fixed inconsistent return type annotations in `BrowserTemplate` implementations.
- Addressed PSR-12 and Pint style conflicts to ensure consistent code formatting.

## [1.0.0] - 2026-01-29

### Added
- **Core Foundation**: `GenerationSpec`, `SpecValidator`, and Enum definitions.
- **Templates**: Support for Chrome, Firefox, Safari, and Edge browsers.
- **Catalogs**: Weighted catalogs for Locales, Device Models, and CPU Architectures.
- **Pickers**: Intelligent selection logic for Version, Model, Locale, and Architecture.
- **Strategies**: 
  - `UniformStrategy`: Equal probability selection.
  - `WeightedStrategy`: Market-share based selection (default).
  - `RoundRobinStrategy`: Sequential cycling.
  - `AvoidRecentStrategy`: History-aware selection.
- **Service Layer**: 
  - `UserAgentService` main facade.
  - `UserAgentRenderer` for string interpolation.
  - Profile shortcuts (`DesktopChrome`, `MobileSafari`, `AndroidChrome`).
- **Filters**: Comprehensive filtering system (Browser, Device, OS, Engine, Version, RiskLevel).
- **History**: `LruHistory` for tracking and avoiding recent UAs.

### Security
- Comprehensive validation against SQL injection, XSS, and path traversal in spec inputs.
- Strict type safety and immutable value objects.
