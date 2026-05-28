# Architecture

## Internal Module Contracts

This modular monolith keeps domain boundaries explicit without extracting services.

Contract policy:

- Module public APIs are service-first (not controller-to-controller).
- Cross-module communication should prefer events and explicit service contracts.
- No direct frontend-specific logic inside domain services.
- No microservice extraction in this phase.

### Auth / Identity Module

- Responsibility: session/token authentication, current user identity, auth guards.
- Public services/contracts: `AuthController` API contract, Sanctum token/session flows.
- Consumed events: user lifecycle updates affecting auth context.
- Emitted events: auth/token lifecycle events.
- Allowed dependencies: Users/RBAC, Monitoring, Security policy config.
- Forbidden dependencies: Chat internals, controller-to-controller calls.
- Data ownership: auth sessions, personal access token lifecycle.
- Extraction readiness notes: boundary is stable; externalization should keep `/api/v1/auth/*` contract unchanged.

### Users / RBAC Module

- Responsibility: users, roles, permissions, effective permissions cache.
- Public services/contracts: `PermissionCacheService`, `RbacMaintenanceService`, user/role/permission APIs.
- Consumed events: user/role/permission change events.
- Emitted events: role/permission/user authorization-related events.
- Allowed dependencies: Auth identity lookup, Activity logging, Notifications integration.
- Forbidden dependencies: direct writes into Chat-owned aggregates.
- Data ownership: users, roles, permissions, mapping/pivot authorization data.
- Extraction readiness notes: maintain permission middleware and cache invalidation boundary as contract surface.

### Dashboard / Stats Module

- Responsibility: dashboard counters and aggregated stats endpoints.
- Public services/contracts: stats API endpoints and response envelope contracts.
- Consumed events: activity/notifications/chat events for aggregate updates.
- Emitted events: none required for current foundation.
- Allowed dependencies: Activity, Notifications, Chat read-only aggregate queries.
- Forbidden dependencies: mutation of foreign module data.
- Data ownership: aggregate/query projection data only.
- Extraction readiness notes: keep query-only boundary and avoid ownership leakage.

### Activity Module

- Responsibility: audit/activity timeline and integration events.
- Public services/contracts: activity API endpoint contracts and event handlers.
- Consumed events: auth/user/rbac/chat/notification lifecycle events.
- Emitted events: activity stream notifications where configured.
- Allowed dependencies: all domain events as inputs.
- Forbidden dependencies: direct business mutation in source modules.
- Data ownership: activity log records.
- Extraction readiness notes: event-consumer boundary already aligned for future extraction.

### Notifications Module

- Responsibility: notification CRUD/read state/preferences and unread counters.
- Public services/contracts: notification endpoints and preference update contracts.
- Consumed events: auth/user/chat/activity events that trigger notifications.
- Emitted events: notification lifecycle events/realtime notifications.
- Allowed dependencies: Auth identity, Realtime broadcast abstraction, Activity integration.
- Forbidden dependencies: direct mutation of Chat core entities.
- Data ownership: notifications and user notification preferences.
- Extraction readiness notes: keep notifier API and event contracts stable.

### Chat Module

- Responsibility: conversations, messages, participants, attachments, read/delivery states.
- Public services/contracts:
  - `ChatConversationService`
  - `ChatMessageService`
  - `ChatReadStateService`
  - `ChatWebhookDeliveryService`
  - `ChatAttachmentService`
  - `ChatPresenceService`
- Consumed events: identity/permission lookups, notification/activity integration triggers.
- Emitted events: chat conversation/message/participant/attachment/realtime/webhook-related events.
- Allowed dependencies: Users/RBAC checks, Notifications dispatch, Activity integration, Realtime broadcast.
- Forbidden dependencies: controller-to-controller orchestration, frontend rendering logic, raw cross-module DB writes outside service boundary.
- Data ownership: chat conversations/messages/participants/attachments/read/delivery/webhook endpoint metadata.
- Extraction readiness notes: service surface is explicit; keep cross-module calls through contracts/events.

### Webhooks / External API Module

- Responsibility: webhook endpoint management, webhook delivery status, external message ingestion.
- Public services/contracts:
  - `ChatWebhookSigningService`
  - `ChatWebhookReplayProtectionService`
  - `ExternalChatMessageService`
  - `ExternalChatTokenService`
- Consumed events: chat lifecycle events for outgoing webhook delivery.
- Emitted events: webhook delivery lifecycle updates and callback events.
- Allowed dependencies: Chat public services, Security rate-limit/signature policy, Queue jobs.
- Forbidden dependencies: exposing token hashes/secrets across module boundaries.
- Data ownership: webhook endpoints/deliveries, external message mapping metadata.
- Extraction readiness notes: signature/replay/token scope contracts are primary extraction boundary.

### Realtime Module

- Responsibility: channel authorization and realtime broadcast signaling.
- Public services/contracts: broadcast channel policy in `routes/channels.php`, realtime service layer, realtime jobs.
- Consumed events: chat/notification/activity events.
- Emitted events: private/presence channel broadcasts.
- Allowed dependencies: Auth identity, Chat access policy, Notification events.
- Forbidden dependencies: leaking sensitive payload fields in presence/realtime events.
- Data ownership: realtime presence state/protocol-level signaling only.
- Extraction readiness notes: keep channel auth and payload safety contract stable before transport changes.

### Monitoring Module

- Responsibility: liveness/readiness checks and safe operational status.
- Public services/contracts: `MonitoringHealthService`, `/health`, protected monitoring endpoint.
- Consumed events: none mandatory; reads infra dependency status.
- Emitted events: structured monitoring/error logs.
- Allowed dependencies: database/cache/queue/redis health probes.
- Forbidden dependencies: exposing secrets/env/raw traces.
- Data ownership: monitoring check summaries only.
- Extraction readiness notes: health contract can be externalized behind same endpoint semantics.

### API Docs Module

- Responsibility: OpenAPI generation, docs access control, permission-aware portal/filtering.
- Public services/contracts:
  - `ApiDocsPermissionService`
  - `ApiDocsOpenApiFilterService`
  - docs access middleware/gates
- Consumed events: RBAC permission changes (for docs visibility semantics).
- Emitted events: none mandatory (read-oriented module).
- Allowed dependencies: RBAC permission checks, monitoring-safe logging.
- Forbidden dependencies: bypassing docs permission model or exposing raw docs to limited users.
- Data ownership: docs permission map/config and filtered docs view logic.
- Extraction readiness notes: keep `/docs/api*` access policy and filtering rules as stable contract.

### Settings / Translations Module

- Responsibility: runtime settings and translation management/preload.
- Public services/contracts:
  - `SettingsService`
  - `TranslationService`
  - `Localization` runtime preload endpoints
- Consumed events: user/role changes that affect effective settings visibility.
- Emitted events: settings/translation update events where applicable.
- Allowed dependencies: Auth/RBAC guards, cache invalidation services.
- Forbidden dependencies: direct mutation of unrelated module owned data.
- Data ownership: system settings and translation records.
- Extraction readiness notes: keep settings/translation API shape and preload contracts stable.

## Event-Driven Module Communication

This modular monolith uses event-driven communication to reduce coupling without extracting services.

### Event taxonomy

1. Domain Events
   - Internal Laravel/PHP events for module decoupling.
   - Should carry IDs and safe metadata rather than full payload blobs.
   - Examples in current codebase: `ActivityLogged`, `PermissionChanged`, `RolePermissionsChanged`, `ChatMessageCreated`.

2. Queue Jobs
   - Async side effects and integration work.
   - Must use retry/backoff/failure handling and keep idempotency where relevant.
   - Must not mutate HTTP response contracts directly.
   - Examples: `DeliverChatWebhookJob`, `CreateNotificationJob`, realtime broadcast jobs.

3. Broadcast Events
   - Realtime UI synchronization events over private/presence channels.
   - Channel authorization is required.
   - Payloads must be safe/minimal and aligned with realtime payload policy.

4. Webhook Events
   - External delivery contract events for integrations.
   - Must respect signature, replay protection, rate limits, and safe payload policy.
   - Event names should remain versionable and contract-stable.

5. Activity/Audit Events
   - High-signal timeline events for observability and audit.
   - Should include safe metadata only (no secrets/raw payloads).

### Allowed communication paths

- Controller -> Service in the same module boundary.
- Service -> Domain Event dispatch.
- Event listener / queue job -> public contract/service of another module.
- Module -> queue job for async side effects.
- Module -> another module public contract/service (explicit boundary calls).

### Avoid / forbidden communication paths

- Controller -> controller calls.
- Module -> another module private service bypassing boundary contracts.
- Raw DB writes into another module-owned tables.
- Broadcast/webhook events with raw domain payload dumps.
- Passing full Eloquent models across boundaries where IDs are sufficient.
- Heavy sync side effects inside read/list APIs.

### Event naming rules

- Use `module.entity.action` naming where possible.
- Examples:
  - `chat.message.created`
  - `chat.participant.blocked`
  - `notification.created`
  - `activity.logged`

### Payload safety rules

- Prefer IDs over full models.
- Use safe scalar metadata and contracted fields only.
- Never include:
  - token/authorization/cookie
  - secret/signature/webhook_secret
  - raw_payload/raw_response
  - file `disk`/`path`/`checksum`
  - `device_key`/`user_agent`/`ip_address` unless explicitly justified
- Keep webhook/external payloads versionable and backward-compatible.

### Modular monolith note

This is a modular monolith foundation. Event contracts are prepared for future extraction readiness, but no microservice split is performed in this phase.

## Service Boundaries

This section defines service boundary rules for the modular monolith without mass refactoring or premature interface extraction.

### Public Module Services

These are contract-like services that other modules may call directly.

- Chat:
  - `ChatConversationService`
  - `ChatMessageService`
  - `ChatReadStateService`
  - `ChatAttachmentService`
- Activity:
  - `ActivityService`
- Notifications:
  - `NotificationService`
  - `NotificationPreferenceService`
- RBAC / Access:
  - `PermissionService`
  - `RoleService`
  - `UserService`
  - `ApiDocsPermissionService`
- Monitoring / System:
  - `MonitoringHealthService`
  - `SystemHealthService`
- API Docs:
  - `ApiDocsOpenApiFilterService`

### Internal Module Services

These services are internal by default and should not be called directly across module boundaries.

- Query services (for example `ChatConversationQueryService`, `SettingsQueryService`)
- Cache services (for example `MetaCacheService`, `PermissionCacheService`, `SettingsCacheService`, `TranslationCacheService`)
- Payload builders and formatters (for example `TranslationPayloadBuilder`, `TranslationFormatterService`)
- Sanitizers and safety helpers (for example `StructuredLogContextService`)
- Realtime helpers and delivery internals (for example `ChatPresenceService`, `ChatWebhookSigningService`, `ChatWebhookReplayProtectionService`)

### Infrastructure Services

Infrastructure services provide platform capabilities and cross-cutting support.

- Logging and monitoring (`RealtimeLogService`, `StructuredLogContextService`, monitoring health checks)
- Cache and queue integration services
- API docs access/filter helpers
- Translation/settings resolver helpers used as platform support

### Allowed call patterns

- Controller -> public module service.
- Public module service -> internal service within the same module.
- Listener/job -> public module service.
- Module -> another module public service/contract.
- Cross-module side effects through events/jobs where practical.

### Avoid / forbidden call patterns

- Controller -> internal helper service of another module.
- Controller -> controller calls.
- Service -> HTTP controller.
- Service -> raw HTTP request object outside the HTTP boundary.
- Cross-module raw DB writes into another module-owned tables.
- Passing full Eloquent models across module boundaries when ID/DTO is enough.

### Naming/marker convention

- `*QueryService`, `*CacheService`, `*PayloadBuilder`, `*Sanitizer`, `*Mapper` are internal by default.
- `*Service` can be public only if explicitly listed in this architecture document.
- Contracts/interfaces are optional and added only for clear extraction readiness; no mass interface extraction in this phase.
