# AGENTS.md

This file provides guidance for agentic coding assistants operating in this repository.
Follow these instructions when reading, modifying, or adding code.

---

## Project Overview

- PHP 8.2+ CLI application built with **Laravel Zero**
- Purpose: analyze PHP class dependencies, coupling, instability, and cycles
- Architecture: layered (Application / Domain / Infrastructure)
- Testing: **Pest** (on top of PHPUnit)
- Formatting: **Laravel Pint**

---

## Environment & Prerequisites

- PHP >= 8.2
- Composer
- Xdebug (optional, for coverage)

Install dependencies:

- `composer install`

---

## Build, Lint, and Test Commands

### Running the Application

- Main binary: `class-dependencies-analyzer`
- Example:
  - `php class-dependencies-analyzer analyze:class app`

### Tests (Pest)

- Run full test suite:
  - `composer test`
  - `vendor/bin/pest -p`

- Run a single test file:
  - `vendor/bin/pest tests/Unit/FooTest.php`

- Run a single test by name:
  - `vendor/bin/pest --filter="it does something"`

- Run a specific testsuite:
  - `vendor/bin/pest --testsuite=Unit`

- Parallel execution is enabled by default via `-p`

### Coverage

- Run tests with coverage:
  - `composer coverage`

### Linting / Formatting

- Format code using Pint:
  - `vendor/bin/pint`

- Check formatting without writing:
  - `vendor/bin/pint --test`

### Healthcheck Scripts

Defined in `composer.json`:

- `composer healthcheck`
- Includes multiple analyzer self-checks and a test run

---

## Code Style Guidelines

### General

- Follow **PSR-12** and Laravel conventions
- Prefer clarity over cleverness
- Keep classes small and single-purpose

### Imports

- Use fully-qualified imports (`use ...`) at top of file
- One import per line
- Remove unused imports
- Group imports logically (PHP, App, Vendor)

### Formatting

- Enforced by **Laravel Pint**
- 4 spaces indentation
- One class per file
- Trailing commas in multiline argument lists

### Naming Conventions

- Classes: `StudlyCase`
- Methods: `camelCase`
- Variables: `camelCase`
- Constants: `SCREAMING_SNAKE_CASE`
- Interfaces: descriptive nouns (no `Interface` suffix preferred)

### Types & Signatures

- Always use scalar and object type hints
- Always declare return types
- Prefer `readonly` and promoted constructor properties where applicable
- Avoid mixed types unless strictly necessary

### Error Handling

- Use exceptions for exceptional states
- Catch `Throwable` only at application boundaries
- Domain logic should not swallow exceptions
- Present errors via presenters or CLI output, not `echo`

### Null & Defensive Code

- Prefer explicit null checks
- Avoid deeply nested conditionals
- Fail fast when input is invalid

---

## Architecture Rules

### Application Layer

- Orchestrates use cases
- Depends on Domain abstractions (ports)
- No infrastructure details

### Domain Layer

- Contains core business logic
- Framework-agnostic
- No IO, no framework dependencies

### Infrastructure Layer

- Implements ports (filesystem, CLI, adapters)
- Can depend on frameworks and vendor libraries

### Dependency Direction

- Infrastructure → Application → Domain
- Never the reverse

---

## Testing Guidelines

- Prefer **Unit tests** for domain logic
- Use **Feature tests** for CLI commands and integration
- Tests should be deterministic and isolated
- Use Mockery for mocking ports

---

## Filesystem & Safety Rules

- Do not modify files in `vendor/`
- Do not commit generated reports or artifacts
- Avoid touching unrelated files

---

## Git & Commits

- Do not commit unless explicitly requested
- Follow existing commit message style
- Never rewrite history without permission

---

## Agent Behavior Expectations

- Respect this file for all edits in this repository
- Keep changes minimal and focused
- Ask before making large refactors
- Do not introduce new tools or dependencies without approval

---

End of AGENTS.md
