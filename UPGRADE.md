# Upgrade guide

## From 0.x to 1.0

Version 1.0 replaces the four commands of 0.x (`weakness`, `cyclic`, `analyze:class`, `analyze:component`) with a single `analyze` command. The dependency graph is computed once and every report is built from it. The metrics follow Robert C. Martin's definitions more strictly, so some values change (see [Metric changes](#metric-changes)).

### Commands

| 0.x | 1.0 |
| --- | --- |
| `weakness app` | `analyze app --report=sdp`, and `Rules::stableDependencies()` in `dependencies.php` to fail the build |
| `weakness app --min-delta=0.1` | `Rules::stableDependencies(0.1)`: the tolerance replaces the minimum delta |
| `cyclic app` | `analyze app --report=cycles`, and `Rules::noCycles()` in `dependencies.php` to fail the build |
| `analyze:class app` | `analyze app --report=classes` |
| `analyze:component app 'App\Domain,App\Application'` | `analyze app --report=components`, with the components declared by `components()` in `dependencies.php` |
| `--graph` | `--format=html --output=dependencies.html`, then open the file in a browser |
| `--only=…` / `--exclude=…` | Kept, see below |

`analyze` without `--report` shows the components, the cycles and the SDP violations. Several sections can be combined: `--report=classes,components,cycles,sdp`.

The following options are removed:

- `--limit`, `--target`, `--depth-limit`: filter the JSON output (`--format=json`) instead.
- `--min-delta`: use the tolerance of `Rules::stableDependencies()`.
- `--debug`, `--info`, `--human-readable`: the table output always shows raw metrics, parsing errors are counted in the report header.

### `--only` and `--exclude`

Both options still take comma-separated values, but they are now FQCN globs anchored at both ends, and they can be combined (`--only` is applied first, then `--exclude`):

```
php class-dependencies-analyzer analyze app --only='App\Domain\*,App\Application\*' --exclude='App\Domain\Tests\*'
```

`App\Domain` matches the `App\Domain` class only: add `\*` to match a namespace. On the command line, they replace `only()` and `ignore()` from `dependencies.php`.

### Configuration file

The project is now described by a `dependencies.php` file at its root: paths, excluded files, components and rules. Without this file, `analyze` takes the paths as arguments and resolves components by namespace (two segments). See [docs/ci.md](docs/ci.md) for every option.

```php
<?php

use App\Core\Config\Config;
use App\Core\Rules\Rules;

return Config::make()
    ->paths(['app'])
    ->components([
        'Domain' => 'App\Domain\*',
        'Application' => 'App\Application\*',
    ])
    ->rules([
        Rules::noCycles(),
        Rules::stableDependencies(),
    ]);
```

### Metric changes

- **Efferent coupling (Ce)** only counts dependencies on analyzed classes. Vendor and native PHP classes are listed as external dependencies but no longer weigh on Ce, so the instability `I = Ce / (Ca + Ce)` is no longer biased towards 1 for classes using the framework.
- **Components** follow Martin: `Nc` (classes), `Na` (abstract classes and interfaces), `A = Na / Nc`, `Ca` and `Ce` counted in classes outside the component, `I`, and the distance from the main sequence `D = |A + I - 1|`. Dependencies between classes of the same component are ignored.
- **Stable Dependencies Principle** is checked between components, not between classes: a violation is a component depending on a less stable component.
- **Cycles** are strongly connected components (Tarjan) of more than one class or component. A class depending on itself is no longer reported as a cycle.
- Metrics are raw floats, rounded only for display.

### Exit codes

0.x always exited with `0`. `analyze` can now fail a CI job:

| Code | Meaning |
| --- | --- |
| `0` | No rule configured, every rule passes, or every violation is in the baseline |
| `1` | At least one rule violation absent from the baseline |
| `2` | Invalid configuration, option or baseline file |

A baseline (`--generate-baseline`) freezes the existing violations so that only new ones fail the build, see [docs/ci.md](docs/ci.md#baseline).
