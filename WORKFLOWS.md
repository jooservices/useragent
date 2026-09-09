# GitHub Actions workflows

All workflows run on GitHub-hosted `ubuntu-latest` runners. PHP commands use the repository Docker Compose environment.

| Workflow | Trigger | Purpose |
| --- | --- | --- |
| CI | Pull requests to `master` or `develop` | Composer validation, lint, tests, coverage, audit, and final quality gate |
| CI post-merge | Push to `master` or `develop` | Validate the merged code with the local CI command |
| Commitlint | Pull request activity | Validate all commit messages |
| Semantic PR Title | Pull request activity | Validate Conventional Commit-shaped PR titles |
| CodeQL | Pull requests, branch pushes, weekly | Analyze GitHub Actions |
| Workflow audit | Workflow changes and weekly | Run Actionlint and Zizmor |
| OpenSSF Scorecard | Push to `develop`, weekly, manual | Publish repository security scorecard |
| Release | Version tags | Verify the tag is on `master`, run the quality gate, create GitHub Release notes |

`scorecard.yml` runs on push to `develop`, weekly, and manual dispatch.

Branch protection is configured after green checks have established their names. No workflow uses self-hosted runners.
