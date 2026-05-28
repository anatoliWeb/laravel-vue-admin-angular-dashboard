# Microservices Preparation (Future)

Current strategy remains a modular monolith. This document is a planning artifact for future extraction decisions only.

## Extractable Domains

### Notifications

- Domain name: Notifications
- Extraction priority: Medium
- Business/technical reason: high async volume, delivery channels, independent scaling potential.
- Current module owner: Notifications module (`NotificationService`, `NotificationPreferenceService`, notification jobs/events).
- Current dependencies: Auth/Identity, Realtime, Activity, Users/RBAC.
- Current data ownership: notifications and notification preferences.
- Required API/async contracts: stable notification API, event schema, delivery status/retry contract.
- Migration complexity: Medium
- Operational complexity: Medium
- Extraction blockers: preference coupling with users, realtime coupling, delivery idempotency hardening.
- Readiness level: L3
- Recommended decision: Candidate later.

### Realtime/WebSocket

- Domain name: Realtime/WebSocket
- Extraction priority: Medium
- Business/technical reason: traffic profile and scaling concerns differ from core request/response API.
- Current module owner: Realtime module (`routes/channels.php`, broadcast events/jobs, `RealtimeLogService`).
- Current dependencies: Auth/Identity, Chat, Notifications, Activity.
- Current data ownership: presence/protocol-level signaling state.
- Required API/async contracts: channel authorization contract, safe presence payload contract, broadcast event versioning.
- Migration complexity: Medium/High
- Operational complexity: High
- Extraction blockers: auth coupling, channel authorization consistency, incident/debug complexity.
- Readiness level: L2/L3
- Recommended decision: Candidate later.

### External Webhooks

- Domain name: External Webhooks
- Extraction priority: Medium/High
- Business/technical reason: integration-specific reliability concerns (retry, replay, signature validation).
- Current module owner: Webhooks/External API module (`ChatWebhookDeliveryService`, `ChatWebhookSigningService`, replay/signature services).
- Current dependencies: Chat domain events, queue subsystem, security policies.
- Current data ownership: webhook endpoints, delivery history, callback metadata.
- Required API/async contracts: callback signature contract, idempotency contract, delivery status/retry contract.
- Migration complexity: Medium
- Operational complexity: Medium/High
- Extraction blockers: token scope lifecycle coupling, callback reliability guarantees, ordering expectations.
- Readiness level: L3
- Recommended decision: Candidate later.

### Activity/Audit

- Domain name: Activity/Audit
- Extraction priority: Medium
- Business/technical reason: naturally event-driven append-only domain.
- Current module owner: Activity module (`ActivityService`, activity listeners/events).
- Current dependencies: Auth, Users/RBAC, Chat, Notifications.
- Current data ownership: activity log records and safe audit metadata.
- Required API/async contracts: canonical activity event schema, retention/archive policy contract.
- Migration complexity: Low/Medium
- Operational complexity: Medium
- Extraction blockers: event ordering/duplication guarantees, schema consistency across emitters.
- Readiness level: L3
- Recommended decision: Candidate later.

### Auth/Identity

- Domain name: Auth/Identity
- Extraction priority: Low/Medium
- Business/technical reason: long-term identity centralization is possible in larger platform setups.
- Current module owner: Auth module (`/api/v1/auth/*`, session/token lifecycle, Sanctum flows).
- Current dependencies: Users/RBAC, Monitoring, Security policies.
- Current data ownership: sessions, personal access tokens, identity context.
- Required API/async contracts: stable identity API, token/session validation contract, revocation semantics.
- Migration complexity: High
- Operational complexity: High
- Extraction blockers: highest blast radius, latency/security risk, broad cross-module dependency.
- Readiness level: L2
- Recommended decision: Not now.

### Chat

- Domain name: Chat
- Extraction priority: Low now, potentially High later
- Business/technical reason: high-volume and complex aggregate boundaries (messages, participants, read/delivery, realtime, webhooks).
- Current module owner: Chat module (`ChatConversationService`, `ChatMessageService`, `ChatReadStateService`, chat webhook/realtime services).
- Current dependencies: Users/RBAC, Notifications, Realtime, Activity, External Webhooks.
- Current data ownership: conversations, messages, participants, attachments, read state, delivery state.
- Required API/async contracts: strict conversation/message contract, participant authorization contract, webhook/realtime payload versioning.
- Migration complexity: High
- Operational complexity: High
- Extraction blockers: data consistency, permission coupling, race conditions under load, migration risk.
- Readiness level: L2/L3
- Recommended decision: Not now, reassess later with load and team readiness.

### API Docs/Monitoring

- Domain name: API Docs/Monitoring
- Extraction priority: Low
- Business/technical reason: tooling-like modules can be separated later if platform scope expands.
- Current module owner: API Docs + Monitoring modules (`ApiDocsPermissionService`, `ApiDocsOpenApiFilterService`, `MonitoringHealthService`).
- Current dependencies: RBAC and app runtime checks.
- Current data ownership: policy/config and health summaries.
- Required API/async contracts: stable docs access/filter contract, health endpoint contract.
- Migration complexity: Low
- Operational complexity: Medium
- Extraction blockers: low product value vs added operational overhead.
- Readiness level: L2
- Recommended decision: Not now.

## Extraction Decision Matrix

| Domain | Readiness Level | Priority | Coupling | Data Ownership Complexity | Async Need | Operational Risk | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Notifications | L3 | Medium | Moderate | Medium | High | Medium | Candidate later |
| Realtime/WebSocket | L2/L3 | Medium | High | Medium | High | High | Candidate later |
| External Webhooks | L3 | Medium/High | Moderate | Medium | High | Medium/High | Candidate later |
| Activity/Audit | L3 | Medium | Moderate | Low/Medium | High | Medium | Candidate later |
| Auth/Identity | L2 | Low/Medium | High | High | Medium | High | Not now |
| Chat | L2/L3 | Low now / High later | High | High | High | High | Not now, reassess later |
| API Docs/Monitoring | L2 | Low | Low/Moderate | Low | Low | Medium | Not now |

## Extraction Anti-Patterns

Do not extract if one or more of the following are true:

- module boundaries are not stable;
- data ownership is unclear;
- cross-service DB writes would be needed;
- distributed transactions would be required for core flows;
- observability and operational runbooks are not ready;
- the team cannot operate additional service lifecycle safely;
- latency/security impact is not measured and understood.

Explicitly avoid:

- shared database microservices;
- direct cross-service DB writes;
- distributed transactions as the default integration strategy.

## Current Position

- Modular monolith remains the current architecture strategy.
- No microservice extraction is performed in this phase.
- No Kafka/RabbitMQ adoption is introduced in this phase.
