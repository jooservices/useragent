# jooservices/useragent

This file adds project-only rules.

- PHP `>= 8.5`; tooling runs in `php:8.5-cli-bookworm` through Docker Compose.
- The package generates deterministic synthetic User-Agent profiles from its bundled, checksummed dataset; it has no HTTP runtime dependency.
- Dataset changes must update the checksum, provenance, and golden fixtures in the same pull request.
