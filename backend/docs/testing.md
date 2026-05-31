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

## API Test Strategy

- Use `ApiContractSmokeTest` for fast API-level smoke coverage across health, auth, permissions, response envelopes, pagination metadata, validation errors, safe 404s, and permission-aware docs access.
- Use `OpenApiRouteContractTest` and `OpenApiResponseEnvelopeTest` for route/spec/envelope contract checks.
- Use focused Security tests for rate limiting, headers, validation hardening, token safety, and realtime channel authorization instead of duplicating those assertions in API smoke tests.
- Use domain suites such as `Chat`, `Notification`, `UsersApi`, and `ActivityApi` for lifecycle-specific behavior.
- Run `composer test:api` or `php -d memory_limit=512M artisan test --filter=Api --stop-on-failure` before release checks when time allows.
- Prefer targeted API filters locally if the full `Api` filter is slow, but do not mark API infrastructure work complete until the targeted contract tests pass.

## Auth Test Strategy

- Use `AuthContractTest` for consolidated auth contract coverage across Vue Admin session-first auth and bearer token fallback flows.
- Keep session tests focused on login, `/api/v1/auth/session/me`, logout, invalid credentials, and safe validation/authentication errors.
- Keep bearer tests focused on `/api/v1/auth/login` or `/api/v1/auth/token`, `/api/v1/auth/me`, logout revocation, revoked token denial, and standardized `401`/`422` envelopes.
- Use `SecurityTokenSecurityTest` for deeper token hashing, one-time reveal, scope, revocation, and no-plain-token storage guarantees.
- Use `SecurityRateLimitingTest` for auth throttling and safe `429` behavior instead of duplicating rate-limit loops in auth contract tests.
- Use `OpenApiAuthEndpointsTest` for auth documentation/spec coverage.
- Frontend auth store/service behavior is covered by Vue Admin npm tests and should be expanded under the separate frontend integration testing task.

## RBAC Test Strategy

- Use `RbacContractTest` as a consolidated RBAC contract suite for permission foundation, role coverage, safe `401/403/200` access behavior, and middleware/gate contracts.
- Keep permission seeding checks lightweight and focused on core permissions (`api.docs.view`, `api.docs.view.full`, `system.monitoring`, and chat/RBAC-critical keys).
- Use route middleware contract assertions to verify `auth:sanctum` + permission middleware on users/roles/permissions endpoints.
- Use cache-version checks (not full cache internals) to verify role/user permission changes invalidate effective-permission cache through version bumps.
- Keep docs access permission behavior in `OpenApiDocsAccessTest`; only add lightweight gate/middleware checks in RBAC contract tests.
- Keep chat-specific RBAC lifecycle behavior in chat suites (for example `ChatRbacPermissionTest`) to avoid duplicating conversation/message flows here.

## Queue Test Strategy

- Use `QueueContractTest` for consolidated queue infrastructure contracts: env-driven queue connection, Redis support, failed jobs tracking, priority queue names, supervisor/Horizon queue order, and queue command documentation.
- Keep critical job contract checks lightweight and static in queue contract tests (for example `DeliverChatWebhookJob` queue/tries/backoff/timeout and safe serialized state).
- Use `QueueLoggingTest` for runtime safe logging assertions and sensitive key stripping checks.
- Use `QueuePerformanceOptimizationTest` for queue performance baseline and worker runtime flags.
- Keep webhook delivery lifecycle, retry scheduling, and external delivery behavior in dedicated chat suites (`ChatWebhookDelivery*`) to avoid duplicated queue-lifecycle logic.
- Keep queue/realtime dispatch-path behavior in existing domain suites (for example `RealtimeQueueTest`) and only add queue-contract assertions here.

## When to use `migrate:fresh`
- CI/full clean validation: acceptable.
- Local targeted reruns: avoid manual `migrate:fresh` before every command.
- `RefreshDatabase` already handles isolated test state inside each run.

## Notes on occasional DB deadlocks/table lifecycle issues
- Prefer sequential runs only.
- Avoid overlapping test processes/containers on the same DB.
- If a run aborts unexpectedly, rerun the same suite once after `composer test:preflight`.
