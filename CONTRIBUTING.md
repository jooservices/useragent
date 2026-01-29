# Contributing to UserAgent

Thank you for your interest in contributing to the UserAgent library! We welcome contributions from the community.

## Reporting Bugs

If you find a bug, please open an issue in the issue tracker. Please include:
- A description of the issue.
- Steps to reproduce the issue.
- The expected behavior.
- The actual behavior.

## Pull Requests

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Write tests for your changes.
4. Ensure all tests pass.
5. Submit a pull request.

## Coding Standards

We follow strictly typed PHP standards. Please ensure:
- **PHPStan**: Level 9 (run `composer lint:phpstan`).
- **Coding Style**: PSR-12 via Pint/PHPCS (run `composer lint`).
- **Tests**: 100% coverage is required for new features.

## Running Tests

```bash
composer test
```
