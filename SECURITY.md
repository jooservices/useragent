# Security policy

Security fixes are supported on the latest stable major. Report vulnerabilities
privately to `security@jooservices.com`; do not include secrets or exploit data
in a public issue.

The package performs no network access and accepts no caller-provided dataset
path. Bundled JSON is schema-validated, checksum-verified, and rendered with
header-safety, control-character, and length limits.
