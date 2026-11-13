# Continuous integration

The `analyze` command checks dependency rules declared in a `dependencies.php` file and fails with a non-zero exit code when a rule is violated. A baseline lets you adopt the rules progressively, and the SARIF output shows the violations in the code scanning interface of GitHub.

## Configuration: `dependencies.php`

Place a `dependencies.php` file at the root of the project (it is picked up automatically, or pass `--config=path/to/file.php`). It returns a `Config` built with fluent, immutable methods:

```php
<?php

use App\Core\Config\Config;
use App\Core\Rules\Rules;

return Config::make()
    // Files or directories to analyze, relative to the project root (required).
    ->paths(['app'])

    // Optional: file globs to skip, matched against paths relative to the root.
    ->exclude(['app/Providers/*'])

    // Optional: FQCN globs to keep (only) or to ignore.
    ->ignore(['App\Presenter\*'])

    // Components: name => FQCN glob(s). A class matching no glob belongs to the
    // component given by its namespace, truncated to componentDepth() segments (2 by default).
    ->components([
        'Domain' => 'App\Domain\*',
        'Application' => 'App\Application\*',
        'Infrastructure' => ['App\Infrastructure\*', 'App\Commands\*'],
    ])

    // Rules checked on every run, none by default.
    ->rules([
        // No dependency cycle between classes or between components.
        Rules::noCycles(),

        // A component only depends on components at least as stable as itself,
        // an instability gap up to the tolerance is accepted.
        Rules::stableDependencies(0.1),

        // Distance from the main sequence at most 0.7, for the listed components
        // only (every component without for()).
        Rules::maxDistanceFromMainSequence(0.7)->for(['Domain', 'Application']),
    ])

    // Optional: baseline file, relative to the project root (see below).
    ->baseline('dependencies-baseline.json')

    // Optional: parse cache, true (default, .dependencies-cache), false, or a file relative to the root.
    ->cache(true);
```

For a single run, the `paths` arguments of the command replace `paths()`, and the `--only` / `--exclude` options (comma-separated FQCN globs) replace `only()` / `ignore()`.

## Running the analysis

```
php class-dependencies-analyzer analyze
```

The report shows the components, cycles and SDP violations, then the rule violations. Useful options:

| Option | Description |
| --- | --- |
| `--config=FILE` | Configuration file, `dependencies.php` of the current directory by default |
| `--format=table\|json\|sarif\|html` | Output format, `table` by default (`html` requires `--output`) |
| `--output=FILE` | Write the report to a file instead of the standard output |
| `--report=SECTIONS` | Comma-separated sections: `classes`, `components`, `cycles`, `sdp` |
| `--baseline=FILE` | Baseline file, replaces the configured one |
| `--generate-baseline[=FILE]` | Write the current violations to a baseline, `dependencies-baseline.json` by default |
| `--no-cache` | Neither read nor write the parse cache |
| `--clear-cache` | Delete the parse cache before the analysis |

## Parse cache

Parsing dominates the run time, so the parse results are cached in `.dependencies-cache` at the root of the project (add it to your `.gitignore`). On the next run, only the files whose content changed are parsed again. The whole cache is ignored after an upgrade of the tool or of its parser, and a corrupted cache is silently rebuilt.

In CI, a fresh checkout starts without cache: the analysis simply parses every file. Caching the file between jobs (for instance with `actions/cache`, keyed on the commit) is optional. Use `--no-cache` to bypass it, or `--clear-cache` to rebuild it from scratch.

## Exit codes

| Code | Meaning |
| --- | --- |
| `0` | No rule configured, every rule passes, or every violation is in the baseline (also after `--generate-baseline`) |
| `1` | At least one rule violation absent from the baseline |
| `2` | Invalid configuration, option or baseline file |

Any non-zero code fails the CI job.

## Baseline

A baseline freezes the existing violations so that only new ones fail the build. Each violation has a stable fingerprint built from the rule and its subject (never from measured values), so a baselined violation stays known while its metrics evolve.

1. Generate it once, from the current state of the project:

   ```
   php class-dependencies-analyzer analyze --generate-baseline
   ```

   This writes `dependencies-baseline.json` (sorted, diffable) and exits with `0`. Pass a value to choose the file: `--generate-baseline=baseline.json`.

2. Commit the file and enable it, either in `dependencies.php` with `->baseline('dependencies-baseline.json')` or on the command line:

   ```
   php class-dependencies-analyzer analyze --baseline=dependencies-baseline.json
   ```

   The report then shows `N baselined, M stale`. Baselined violations are hidden and do not fail the run.

3. Update it when violations are fixed: the fingerprints that match no violation anymore are reported as *stale* without failing the run. Run `--generate-baseline` again and commit the shorter file. Avoid regenerating it to hide a new violation: that is precisely what the baseline is meant to catch.

## Output formats

- `table`: human-readable report, for local runs and CI logs.
- `json`: machine-readable report (schema 1) with a `violations` list (`rule`, `fingerprint`, `message`, `file`).
- `sarif`: [SARIF 2.1.0](https://docs.oasis-open.org/sarif/sarif/v2.1.0/sarif-v2.1.0.html) log for code scanning. It only contains the rule violations (the new ones when a baseline is used) as `error` results, and the parsing errors as `warning` results of the `parse-error` rule; the metric sections are ignored. A violation tied to a class is located in its file; a component-level violation (component cycle, SDP, distance) is located in the configuration file used, or in the first analyzed path without configuration file. The fingerprint is reported in `partialFingerprints` so that code scanning tracks an alert across commits.

```
php class-dependencies-analyzer analyze --format=sarif --output=dependencies.sarif
```

## GitHub Actions

The workflow below fails the job on a new violation and uploads the SARIF report, so the violations appear in the *Security > Code scanning* tab and as annotations on pull requests. The upload step runs even when the analysis fails (`if: always()`).

```yaml
name: dependencies

on: [push, pull_request]

permissions:
  contents: read
  security-events: write

jobs:
  analyze:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Download the analyzer
        run: wget https://github.com/DeGraciaMathieu/php-class-dependencies-analyzer/raw/main/builds/class-dependencies-analyzer

      - name: Analyze dependencies
        run: php class-dependencies-analyzer analyze --format=sarif --output=dependencies.sarif

      - name: Upload the SARIF report
        if: always()
        uses: github/codeql-action/upload-sarif@v3
        with:
          sarif_file: dependencies.sarif
          category: dependencies
```

Code scanning is available on public repositories, and on private ones with GitHub Advanced Security. Without it, drop the upload step: the job still fails on a new violation and the log shows the report with the default `table` format.

## GitLab CI

`artifacts:reports` does not accept SARIF, so keep the JSON report as a plain artifact. The job fails on a new violation, the artifact is kept in every case (`when: always`).

```yaml
dependencies:
  image: php:8.2-cli
  script:
    - curl -sSLO https://github.com/DeGraciaMathieu/php-class-dependencies-analyzer/raw/main/builds/class-dependencies-analyzer
    - php class-dependencies-analyzer analyze --format=json --output=dependencies.json
  artifacts:
    when: always
    paths:
      - dependencies.json
```
