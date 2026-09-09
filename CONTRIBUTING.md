# Contributing

Use PHP 8.5 through Docker. Changes target `develop` through a Conventional
Commit pull request. Run `make ci` and `make audit` before opening the pull
request. Install hooks with `tools/install-git-hooks`. Dataset changes must
update their checksum, provenance, golden fixture, and release notes in the
same change.
