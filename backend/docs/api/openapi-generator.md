# OpenAPI Documentation Generator

## Generator Choice
- Package: `dedoc/scramble`
- Why: Laravel-native OpenAPI generation from routes, controllers, FormRequests and resources, with low annotation overhead.

## Routes
- UI: `/docs/api`
- JSON spec: `/docs/api.json`

## Source of Truth
OpenAPI output is generated from:
- Laravel routes (`routes/api.php`)
- FormRequest validation contracts (`app/Http/Requests/**`)
- API resources (`app/Http/Resources/**`)
- Scramble transformers/security/schema wiring in `AppServiceProvider`
- preparation inventory in `docs/api/openapi-preparation.md`

## Auth and Security in Docs
- `BearerAuth` for protected API routes
- `SanctumSession` for session-first browser flow
- `ExternalChatToken` for external chat API routes
- `WebhookSignature` + `WebhookTimestamp` for incoming webhook verification routes

## Access Control
- Docs middleware is configured in `config/scramble.php`
- `ApiDocsAccessMiddleware` protects `/docs/api` and `/docs/api.json`
- Non-local access requires `api.docs.view` (via gate)

## Permission-Aware API Documentation
- Centralized permission map is defined in `config/api-docs.php`.
- Group resolver/service: `App\Services\ApiDocsPermissionService`.
- Baseline permissions:
  - `api.docs.view`: can open docs routes.
  - `api.docs.view.full`: can see all mapped API groups in future filtered-spec mode.
- Current step is foundation only:
  - no runtime OpenAPI JSON filtering is applied yet.
  - map is used as contract for upcoming permission-aware spec slicing.

## Permission-Aware Docs Portal
- Portal route: `/docs/api/portal`
- Access:
  - local/testing: available for development workflow
  - non-local: protected by `ApiDocsAccessMiddleware` and `api.docs.view`
- Visibility rules:
  - `api.docs.view.full` users see all mapped API groups.
  - other docs users see only groups allowed by `ApiDocsPermissionService`.
  - if user has docs access but no mapped endpoint permissions, portal renders a safe empty state.
- Scope of this step:
  - permission-aware navigation/entry mode is enabled on portal.
  - `/docs/api` (Swagger UI) and `/docs/api.json` remain unchanged and unfiltered.

## Permission-Aware Filtered OpenAPI Spec
- Base spec: `/docs/api.json` (full Scramble output).
- Filtered spec: `/docs/api.filtered.json` (user-scoped visibility mode).
- Access control:
  - docs access still requires `api.docs.view` in non-local.
  - `api.docs.view.full` keeps full path visibility.
- Filtering source:
  - `config/api-docs.php` groups map
  - `App\Services\ApiDocsPermissionService`
  - `App\Services\ApiDocsOpenApiFilterService`
- Filtering scope in current implementation:
  - filters `paths` by current authenticated docs user permissions.
  - keeps valid OpenAPI root (`openapi`, `info`, `paths`, `components`).
  - internal/hidden routes remain excluded.
- Known limitation:
  - `components` are intentionally not aggressively pruned yet and may include broader schemas than visible paths.

## How to Verify Generator Workflow
Run:
- `composer test:openapi`
- `php -d memory_limit=512M artisan test --filter=OpenApiRouteContract --stop-on-failure`

Manual inspect:
- Open `/docs/api` in browser
- Open `/docs/api.json` and verify OpenAPI root fields (`openapi`, `info`, `paths`, `components`)

## Operational Notes
- `test:openapi` is the primary regression gate for docs contract.
- Keep API response envelope and validation error format consistent to preserve generated contract quality.
- Keep docs/spec free of sensitive internals:
  - no `token_hash`, webhook secret values, raw signatures, storage internals.

## What Not To Do
- Do not introduce a second Swagger package (no L5-Swagger).
- Do not add manual annotations everywhere when routes/requests/resources already describe the contract.
- Do not expose docs routes publicly in non-local environments.
