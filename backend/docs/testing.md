# Backend Testing Lifecycle (Local + CI)

## Why targeted runs were slow
- Most feature suites use `RefreshDatabase`.
- Each separate `php artisan test --filter=...` command starts a new PHP process.
- New test process means fresh Laravel bootstrap + migration lifecycle for testing DB.
- Running many small filtered commands in sequence causes repeated cold-start overhead.

## Safety model
- Tests are pinned to `APP_ENV=testing` and testing DB (`saas_testing`) by:
  - `phpunit.xml` env values
  - `tests/TestCase.php` bootstrap env enforcement
  - `TestingDatabaseGuard` fail-fast protection
- Do not run tests in parallel against the same shared `saas_testing` database.

## Memory model
- PHPUnit config sets `memory_limit=512M` for tests.
- Composer test scripts also run with `php -d memory_limit=512M`.
- This avoids intermittent `128M` fatal errors in larger Pest/Scramble/OpenAPI runs.

## Recommended local workflow

### 1. Preflight once per session
```bash
composer test:preflight
```

### 2. Run grouped domain commands (single process per domain)
```bash
composer test:openapi
composer test:chat
composer test:api
composer test:auth
```

Using one grouped command per domain is faster than launching many tiny filtered commands one-by-one.

### 3. Full suite
```bash
composer test
```

## When to use `migrate:fresh`
- CI/full clean validation: acceptable.
- Local targeted reruns: avoid manual `migrate:fresh` before every command.
- `RefreshDatabase` already handles isolated test state inside each run.

## Notes on occasional DB deadlocks/table lifecycle issues
- Prefer sequential runs only.
- Avoid overlapping test processes/containers on the same DB.
- If a run aborts unexpectedly, rerun the same suite once after `composer test:preflight`.
