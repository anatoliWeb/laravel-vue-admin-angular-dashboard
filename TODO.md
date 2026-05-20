# TODO.md - Laravel + Vue Admin + Angular Dashboard SaaS

> Goal: Build a production-grade SaaS platform with Laravel API, Vue admin panel, Angular client dashboard, queues, events, realtime infrastructure, and future microservice scalability.

---

# Phase 0 - Project Foundation

- [x] Create monorepo structure
- [x] Configure Docker environment
- [x] Configure root environment system
- [x] Setup Laravel backend
- [x] Setup Angular frontend container
- [x] Setup Vue admin inside Laravel
- [x] Configure frontend HMR
- [x] Configure queue worker container
- [x] Prepare WebSocket foundation
- [x] Cleanup legacy/demo architecture

---

# Phase 1 - Infrastructure Stabilization

- [x] Fix backend container stability
- [x] Validate nginx <-> php-fpm connectivity
- [x] Validate queue worker startup
- [x] Validate Angular container startup
- [x] Validate Redis connectivity
- [x] Validate MySQL persistence
- [x] Add healthcheck endpoints
- [x] Add container restart strategy
- [x] Add Docker healthchecks

---

# Phase 2 - Backend Core Architecture

- [x] Base API response system
- [x] BaseController
- [x] Global exception handler
- [x] API response standardization
- [x] API versioning (/api/v1)
- [x] Route grouping
- [x] API Resources / Transformers
- [x] Shared response contract
- [x] Modular backend structure preparation

---

# Phase 3 - Vue Admin Foundation

- [x] Prepare Vue admin architecture
- [x] Setup Vue router
- [x] Setup Pinia store
- [x] Create admin layouts
- [x] Create auth layouts
- [x] Create shared UI components
- [x] Create API client layer

### Localization / Translation Foundation

- [x] Setup scalable i18n system
- [x] Create dynamic translation database architecture
- [x] Create system_translations table
- [x] Create SystemTranslation model
- [x] Create TranslationService
- [x] Create dynamic translation helper
- [x] Create translation seeders architecture
- [x] Create RBAC translations
- [x] Create settings translations
- [x] Create dashboard translations
- [x] Create auth translations
- [x] Create validation translations
- [x] Create notification translations
- [x] Integrate backend dynamic translations
- [x] Create translation cache layer
- [x] Create translation API endpoints
- [x] Create translation preload strategy
- [x] Create missing translation fallback logic

### Laravel Vue Admin Localization

- [x] Integrate frontend dynamic translations
- [x] Create Vue i18n dynamic bridge
- [x] Implement runtime locale switching
- [x] Implement API locale propagation
- [x] Implement localized RBAC UI
- [x] Implement multilingual RBAC forms
- [x] Implement RBAC translation persistence
- [x] Implement localized metadata rendering

### Pending

- [x] Create translation admin CRUD
- [x] Create translation synchronization strategy
- [ ] Integrate Angular frontend localization

### Shared UI / Overlay Architecture

- [x] Setup reusable dropdown/overlay system
- [x] Setup reusable pagination system
- [x] Setup reusable dashboard widget system
- [x] Setup realtime-ready frontend architecture
- [x] Setup Laravel SPA bridge
- [x] Validate Vue SPA + HMR workflow

### Core Admin Modules

- [x] Dashboard module
- [x] Users module
- [x] Roles module
- [x] Permissions module
- [x] Tokens module
- [x] Activity module
- [x] Settings module

### Interaction / UX Foundation

- [x] Create BaseModal system
- [x] Create BaseDrawer system
- [x] Create floating panel system
- [x] Create reusable form system
- [x] Create validation layer
- [x] Create async form states
- [x] Create toast notification system
- [x] Create confirm dialog system
- [x] Create optimistic UI support
- [x] Create command palette foundation

### Legacy Migration

- [ ] Gradually replace Blade admin pages
- [ ] Gradually migrate old admin logic
- [x] Validate old/new admin coexistence
- [ ] Remove unused Blade pages after migration
- [x] Cleanup deprecated admin routes

---

### Phase 3.x - Dynamic Settings Architecture

- [x] Create settings database architecture
- [x] Create hierarchical settings resolver
- [x] Create typed settings system
- [x] Create frontend/backend settings separation
- [x] Create settings inheritance engine
- [x] Create settings cache layer
- [x] Create feature flag preparation
- [x] Create settings admin UI
- [x] Create effective value preview system

---

# Phase 4 - Angular Dashboard Architecture

- [x] Create core module structure
- [x] Create shared module
- [x] Create feature module architecture
- [x] Create API client
- [x] Configure environment system
- [x] Create dashboard layout
- [x] Create dashboard widgets foundation
- [x] Create user profile module
- [x] Create settings module foundation
- [x] Create notification center foundation
- [x] Add permission-aware UI
- [x] Prepare realtime widget architecture

---

# Phase 5 - Service Layer

- [x] UserService
- [x] AuthService
- [x] RoleService
- [x] PermissionService
- [x] ActivityService foundation
- [x] NotificationService
- [x] RealtimeService foundation
- [x] SocketService abstraction
- [x] TokenService
- [x] Move logic out of controllers
- [x] Prepare DTO layer
- [x] Prepare Action classes

---

# Phase 6 - Authentication

- [x] Session authentication foundation
- [x] Login endpoint
- [x] Logout endpoint
- [x] Protect API routes
- [x] Angular auth integration
- [x] Vue auth integration
- [x] Shared auth contract
- [x] Token expiration policy hardening
- [x] Remember-me/session persistence hardening
- [x] API token authentication strategy

---

# Phase 7 - RBAC System

- [x] Roles migration
- [x] Permissions migration
- [x] User-role relationships
- [x] Direct permissions support
- [x] Permission middleware foundation
- [x] API authorization layer foundation
- [x] Vue permission guards
- [x] Angular permission guards
- [x] Permission-aware navigation
- [x] Permission caching

---

# Phase 8 - Activity & Audit System

- [x] ActivityLog model
- [x] Activity observers foundation
- [x] Manual activity logging foundation
- [x] Activity API
- [x] Dashboard activity feed foundation
- [x] Admin monitoring page

---

# Phase 9 - Queues & Jobs

- [x] Redis queue configuration
- [x] Queue worker optimization foundation
- [x] Failed jobs handling
- [x] Retry strategy
- [x] Email jobs
- [x] Notification jobs
- [x] Realtime broadcast jobs
- [x] Queue monitoring
- [x] Horizon evaluation and dashboard integration

---

# Phase 10 - Events System

- [x] Domain events
- [x] Event listeners
- [x] Event-driven service actions
- [x] Decouple side effects from services
- [x] Cross-module event architecture

# Future Hardening

## Events System

- [x] Add afterCommit support for critical domain events
- [x] Add domain events for token lifecycle
- [x] Add domain events for notifications lifecycle
- [x] Add event payload versioning policy
- [x] Add more tests for observer/listener duplication risks

---

# Phase 11 - WebSockets & Realtime

### Realtime Foundation

- [x] Frontend realtime architecture
- [x] Angular realtime foundation
- [x] Vue realtime foundation
- [x] WebSocket client preparation

### Realtime Infrastructure

- [x] Configure Laravel Reverb
- [x] Configure broadcasting
- [x] Configure channels
- [x] Configure private channels
- [x] Configure presence channels
- [x] Test realtime events
- [x] Queue-based broadcasting
- [x] Vue realtime updates
- [x] Angular realtime updates
- [x] Realtime notifications
- [x] Realtime dashboard refresh
- [x] Realtime user activity stream

---

# Phase 12 - Notifications System

- [x] Database notifications
- [x] Broadcast notifications
- [x] Vue notifications UI
- [x] Angular notifications UI foundation
- [x] Notification preferences
- [x] Notification read/unread state

---
# Phase 13 - Chat System (Optional)

> Goal: build a flexible chat foundation that supports direct chats, private/public group chats, realtime messaging, file attachments, admin monitoring/replies, external API access, webhooks, presence, typing indicators, participant restrictions, imported history, demo seed data, and device-level read state.

---

## Chat Architecture / Planning

- [x] Chat database schema design
- [x] Discuss and approve table structure before implementation
- [x] Define direct-to-group history import strategy
- [x] Define participant access states and blocking modes
- [x] Define chat seed/demo data strategy
- [x] Define aggregated user-level read state
- [x] Define per-device read state
- [x] Chat system architecture audit
- [x] Define chat permissions and ownership rules
- [x] Define message lifecycle/status model
- [x] Define file attachment rules and storage strategy
- [x] Define external API and webhook strategy
- [x] Define admin monitoring and moderation scope

---

## Direct-to-Group History Strategy

- [x] Define rule: original direct conversation remains unchanged
- [x] Define rule: adding a third participant creates a new private group conversation
- [x] Define history import strategy
  - [x] none
  - [x] from selected date
  - [x] from selected message
  - [x] full history
- [x] Define imported message fields
  - [x] is_imported
  - [x] imported_from_conversation_id
  - [x] imported_from_message_id
- [x] Define imported attachment strategy
  - [x] copied_from_attachment_id
  - [x] is_imported
- [x] Define created_from_conversation_id relation
- [ ] Implement direct-to-group creation service
- [ ] Implement history import service
- [ ] Implement imported history audit logging
- [ ] Add tests for direct-to-group history import

---

## Participant Access / Blocking

- [x] Define participant roles
  - [x] owner
  - [x] admin
  - [x] member
  - [x] viewer
  - [x] support
- [x] Define participant capabilities
  - [x] can_invite
  - [x] can_remove
  - [x] can_send
  - [x] can_attach
  - [x] can_manage
  - [x] can_moderate
- [x] Define participant access states
  - [x] full
  - [x] read_only
  - [x] hidden
  - [x] blocked
- [x] Define block display modes
  - [x] hide_chat
  - [x] show_notice
  - [x] show_read_only_history
- [x] Define blocked fields
  - [x] blocked_by
  - [x] blocked_at
  - [x] blocked_reason
- [x] Define participant history visibility window
  - [x] history_visible_from_message_id
  - [x] history_visible_from_at
  - [x] history_visible_until_message_id
  - [x] history_visible_until_at
- [ ] Implement participant restriction service
- [ ] Implement block/unblock participant actions
- [ ] Add participant access tests

---

## Device-level Read State

- [x] Define aggregated user-level read state
- [x] Define per-device read state
- [x] Define chat user devices table
- [x] Define message device reads table
- [ ] Register/update chat device from frontend
- [ ] Store stable device key on client
- [ ] Track message read state per device
- [ ] Sync aggregated user read state from device reads
- [ ] Show per-device read information in admin, якщо потрібно
- [ ] Add device-level read tests

---

## Chat Backend

- [ ] Chat API foundation
- [x] Conversation model
- [x] Conversation participants model
- [x] Message model
- [x] Message read state
- [x] Device-level message read state
- [x] Message delivery status
- [x] Chat user device model
- [ ] Direct chats
- [ ] Group chats
- [ ] Public/private conversations
- [ ] Conversation type support
  - [ ] direct
  - [ ] group
  - [ ] support/admin
  - [ ] external/API
  - [ ] system
- [ ] Conversation visibility support
  - [ ] private
  - [ ] public
- [ ] Join policy support
  - [ ] invite_only
  - [ ] participants_can_invite
  - [ ] anyone_with_permission
  - [ ] public_join
- [ ] Participant roles
  - [ ] owner
  - [ ] admin
  - [ ] member
  - [ ] viewer
  - [ ] support
- [x] Participant capability checks
- [x] Participant access/blocking checks
- [x] Conversation permissions
- [x] Message ownership checks 
- [ ] Message soft delete
- [ ] Conversation archive/close state
- [ ] Message search foundation
- [ ] Chat backend tests

---

## Chat Database Tables

- [ ] `conversations`
- [ ] `conversation_participants`
- [ ] `messages`
- [ ] `message_reads`
- [ ] `chat_user_devices`
- [ ] `message_device_reads`
- [ ] `message_deliveries`
- [ ] `message_attachments`
- [ ] `external_message_mappings`
- [ ] `chat_webhook_endpoints`
- [ ] `chat_webhook_deliveries`
- [ ] `chat_moderation_logs`
- [ ] `add_chat_message_references_to_conversations_table`
- [ ] `add_chat_message_references_to_conversation_participants_table`
- [x] Review indexes before implementation
- [x] Review unique constraints before implementation
- [x] Review foreign keys before implementation
- [x] Review cascade/delete behavior before implementation
- [ ] Run migrations locally
- [ ] Run migrations in testing database
- [ ] Verify migration rollback

---

## Chat Demo Seed Data

- [ ] Create `ChatDemoSeeder`
- [ ] Add `CHAT_DEMO_SEED` env flag
- [ ] Add `CHAT_DEMO_MESSAGES_COUNT` env setting
- [ ] Prevent fake chat seed in production
- [ ] Seed demo conversations
  - [ ] direct
  - [ ] private group
  - [ ] public group
  - [ ] support/admin
  - [ ] external/API
- [ ] Seed 300+ demo messages
- [ ] Seed message deliveries
- [ ] Seed message reads
- [ ] Seed device-level reads, якщо потрібно
- [ ] Seed imported history example
- [ ] Add safe cleanup for previous demo seed data

---

## Message Attachments

- [ ] Message file attachments foundation
- [ ] Attachment upload endpoint
- [ ] Attachment download endpoint
- [ ] Attachment preview metadata
- [ ] Attachment ownership checks
- [ ] Attachment size limits
- [ ] Allowed MIME types
- [ ] Storage disk configuration
- [ ] Attachment status support
  - [ ] active
  - [ ] deleted
  - [ ] quarantined
  - [ ] failed
- [ ] Imported/copied attachment support
- [ ] Virus/security scan placeholder
- [ ] Image/document/audio support strategy
- [ ] Attachment cleanup on message delete
- [ ] Attachment tests

---

## Chat API

- [ ] List conversations API
- [ ] Create direct conversation API
- [ ] Create group conversation API
- [ ] Create private group from direct conversation API
- [ ] Import direct history into new group conversation API
- [ ] Register/update chat device API
- [ ] Mark message/conversation as read from device API
- [ ] Add/remove participants API
- [ ] Block/unblock participant API
- [ ] Update participant access/capabilities API
- [ ] Load messages API
- [ ] Send message API
- [ ] Edit message API, якщо потрібно
- [ ] Delete message API
- [ ] Mark conversation as read API
- [ ] Upload message attachment API
- [ ] List conversation participants API
- [ ] Leave conversation API
- [ ] Close/archive conversation API
- [ ] API validation
- [ ] API feature tests

---

## Realtime Chat

- [ ] Realtime message created event
- [ ] Realtime message updated event
- [ ] Realtime message deleted event
- [ ] Realtime message read event
- [ ] Realtime device-level read event, якщо потрібно
- [ ] Realtime delivery status event
- [ ] Realtime participant access changed event
- [ ] Private chat channels
- [ ] Presence chat channels
- [ ] Typing indicators
- [ ] Online users state
- [ ] Conversation presence state
- [ ] User joined/left conversation event
- [ ] Realtime attachment notification
- [ ] Realtime tests
- [ ] Queue-based realtime broadcasting

---

## Presence / Online Users

- [ ] Use existing presence foundation from Phase 11
- [ ] `presence-chat.{conversationId}`
- [ ] Show online users in chat sidebar
- [ ] Show who is currently inside conversation
- [ ] Show typing users
- [ ] Show participant online/offline state
- [ ] Show last seen placeholder/foundation
- [ ] Optional device-aware presence foundation
- [ ] Presence payload safe fields only
  - [ ] id
  - [ ] name
  - [ ] avatar, if available and safe
- [ ] Presence cleanup on disconnect
- [ ] Presence tests

---

## Typing Indicators

- [ ] Typing start event
- [ ] Typing stop event
- [ ] Typing debounce/throttle
- [ ] Typing timeout fallback
- [ ] Typing indicator in direct chat
- [ ] Typing indicator in group chat
- [ ] Typing indicator privacy rules
- [ ] Typing tests

---

## External Chat API & Webhooks

- [ ] External API message sending
- [ ] API access tokens/scopes for chat
- [ ] External message id / idempotency support
- [ ] Incoming webhook endpoint for external messages
- [ ] Outgoing webhooks for message events
- [ ] Webhook endpoint management
- [ ] Webhook delivery logs
- [ ] Webhook retry strategy
- [ ] Webhook signature verification
- [ ] Webhook secret rotation foundation
- [ ] Webhook failure handling
- [ ] Webhook replay protection
- [ ] Message delivery/read status callbacks
- [ ] External API rate limiting
- [ ] External API tests

### Webhook Events

- [ ] `message.created`
- [ ] `message.delivered`
- [ ] `message.read`
- [ ] `message.failed`
- [ ] `message.deleted`
- [ ] `conversation.created`
- [ ] `participant.joined`
- [ ] `participant.left`
- [ ] `participant.blocked`
- [ ] `participant.unblocked`
- [ ] `attachment.created`

---

## Angular Chat Module

- [ ] Angular chat module
- [ ] Conversation list
- [ ] Direct chat UI
- [ ] Group chat UI foundation
- [ ] Public/private conversation UI foundation
- [ ] Message thread UI
- [ ] Send message form
- [ ] File attachment upload UI
- [ ] Attachment preview/download UI
- [ ] Message read state UI
- [ ] Register/store stable chat device key
- [ ] Send device key on read actions
- [ ] Participant access notice UI
  - [ ] read_only notice
  - [ ] hidden state handling
  - [ ] blocked notice
  - [ ] show_read_only_history mode
- [ ] Online users sidebar
- [ ] Typing indicators
- [ ] Realtime message updates
- [ ] Realtime read/delivery updates
- [ ] Conversation participants panel
- [ ] Search/filter conversations
- [ ] Empty/loading/error states
- [ ] Angular build/tests

---

## Vue Admin Chat / Monitoring

- [ ] Vue admin chat monitoring page
- [ ] Admin conversation list
- [ ] Admin conversation detail view
- [ ] Admin can reply to conversations
- [ ] Admin can see more metadata than Angular users
- [ ] Admin can see participant info
- [ ] Admin can see participant access state/capabilities
- [ ] Admin can block/unblock participants
- [ ] Admin can set participant read-only mode
- [ ] Admin can hide chat from participant
- [ ] Admin can see message delivery/read state
- [ ] Admin can see per-device read state, якщо потрібно
- [ ] Admin can see imported history markers
- [ ] Admin can see external API source, if message came from API
- [ ] Admin can see webhook delivery status
- [ ] Admin can view attachments
- [ ] Admin can moderate/delete messages, if permission allows
- [ ] Admin can close/archive conversations
- [ ] Admin filters
  - [ ] direct/group/support/external
  - [ ] private/public
  - [ ] unread
  - [ ] assigned/unassigned
  - [ ] blocked/restricted participants
  - [ ] failed webhook delivery
  - [ ] imported messages
- [ ] Vue realtime chat updates
- [ ] Vue build/tests

---

## Admin / Moderation / Audit

- [ ] Chat moderation foundation
- [ ] Message audit logging
- [ ] Conversation audit logging
- [ ] Participant restriction audit logging
- [ ] Attachment audit logging
- [ ] Admin reply audit logging
- [ ] External API message audit logging
- [ ] Webhook delivery audit logging
- [ ] History import audit logging
- [ ] Device-level read audit visibility, якщо потрібно
- [ ] Suspicious message activity placeholder
- [ ] Chat activity integration with existing Activity system

---

## Permissions / RBAC

- [ ] `chat.view`
- [ ] `chat.create`
- [ ] `chat.send`
- [ ] `chat.edit`
- [ ] `chat.delete`
- [ ] `chat.conversations.view`
- [ ] `chat.conversations.create`
- [ ] `chat.conversations.edit`
- [ ] `chat.conversations.close`
- [ ] `chat.conversations.archive`
- [ ] `chat.conversations.delete`
- [ ] `chat.participants.view`
- [ ] `chat.participants.add`
- [ ] `chat.participants.remove`
- [ ] `chat.participants.manage`
- [ ] `chat.attachments.view`
- [ ] `chat.attachments.upload`
- [ ] `chat.attachments.download`
- [ ] `chat.attachments.delete`
- [ ] `chat.admin.view`
- [ ] `chat.admin.reply`
- [ ] `chat.admin.moderate`
- [ ] `chat.admin.delete_messages`
- [ ] `chat.admin.close_conversations`
- [ ] `chat.admin.view_metadata`
- [ ] `chat.external_api.use`
- [ ] `chat.external_api.manage`
- [ ] `chat.external_api.view_logs`
- [ ] `chat.webhooks.view`
- [ ] `chat.webhooks.create`
- [ ] `chat.webhooks.edit`
- [ ] `chat.webhooks.delete`
- [ ] `chat.webhooks.manage`
- [ ] `chat.webhooks.view_deliveries`
- [ ] `chat.webhooks.retry_deliveries`
- [ ] Permission middleware
- [ ] Vue permission-aware navigation
- [ ] Angular permission guards
- [ ] Permission tests

---

## Security

- [x] Conversation ownership checks
- [x] Participant access checks
- [x] Participant capability checks
- [x] Participant blocking/read-only/hidden checks
- [ ] Device key validation and ownership checks
- [ ] Admin access checks
- [ ] External API token scopes
- [ ] Webhook HMAC signatures
- [ ] Webhook replay protection
- [ ] Message attachment access control
- [ ] Attachment MIME validation
- [ ] Attachment size validation
- [ ] Sensitive data policy for messages
- [ ] Safe realtime payloads
- [ ] Safe presence payloads
- [ ] Safe device-level read payloads
- [x] Safe imported history visibility
- [ ] Rate limiting for message sending
- [ ] Rate limiting for external API
- [ ] Abuse/spam protection placeholder

---

## Tests

- [ ] Migration tests / migrate:fresh
- [ ] Seeder tests / local smoke check
- [ ] Conversation API tests
- [ ] Message API tests
- [ ] Direct-to-group history import tests
- [ ] Group chat tests
- [x] Participant access tests
- [x] Participant blocking/read-only/hidden tests
- [x] Message read state tests
- [ ] Device-level read state tests
- [ ] Message delivery state tests
- [ ] Realtime message tests
- [ ] Presence channel tests
- [ ] Typing indicator tests
- [ ] Attachment upload/download tests
- [ ] External API tests
- [ ] Webhook delivery tests
- [ ] Webhook signature tests
- [ ] Admin monitoring tests
- [ ] RBAC tests
- [ ] Full backend suite
- [ ] Vue build
- [ ] Angular build

---

## Future Hardening

- [ ] Message reactions
- [ ] Message edit history
- [ ] Message pinning
- [ ] Message mentions
- [ ] Message threads/replies
- [ ] Push/email notifications for unread messages
- [ ] Chat retention policy
- [ ] Chat export
- [ ] Chat analytics
- [ ] Advanced moderation rules
- [ ] Full text search
- [ ] End-to-end encryption research

---

# Phase 14 - API Improvements

- [x] Pagination foundation
- [x] Filtering foundation
- [x] Sorting foundation
- [x] Search foundation
- [x] Validation standardization foundation
- [ ] OpenAPI preparation
- [ ] API documentation generator

---

# Phase 15 - Performance

- [x] Frontend lazy loading foundation
- [x] API response optimization foundation
- [x] Eager loading optimization foundation
- [ ] Redis caching
- [ ] Query optimization
- [ ] Asset optimization
- [ ] Queue performance optimization

---

# Phase 16 - Security

- [ ] Rate limiting
- [ ] Secure headers
- [ ] Validation hardening
- [ ] Token security
- [x] Permission validation foundation
- [ ] Realtime channel authorization
- [ ] Docker security review

---

# Phase 17 - Logging & Monitoring

- [x] Request logging foundation
- [x] Error logging foundation
- [ ] Queue logging
- [ ] Realtime logs
- [ ] Monitoring preparation
- [ ] Structured logs
- [ ] Container log strategy

---

# Phase 18 - Docker & DevOps

- [ ] Optimize Docker images
- [x] Development configs foundation
- [ ] Production configs
- [x] Environment synchronization foundation
- [x] Container healthchecks
- [x] Startup optimization foundation
- [ ] CI/CD preparation
- [ ] Release workflow preparation

---

# Phase 19 - Modular Architecture Preparation

- [x] Separate domains/modules foundation
- [ ] Prepare internal module contracts
- [ ] Prepare event-driven module communication
- [ ] Prepare service boundaries
- [x] Reduce coupling between domains foundation
- [ ] Prepare future extraction strategy

---

# Phase 20 - Microservices Preparation (Future)

- [ ] Identify extractable domains
- [ ] Prepare API gateway strategy
- [ ] Prepare async communication strategy
- [ ] Prepare auth service strategy
- [ ] Prepare notification service extraction
- [ ] Prepare realtime service extraction
- [ ] Evaluate Kafka/RabbitMQ
- [ ] Prepare observability strategy

---

# Phase 21 - Documentation

- [ ] README.md
- [ ] README_UA.md
- [ ] architecture.md
- [x] api.md foundation
- [ ] commands.md
- [ ] deployment.md
- [ ] realtime.md
- [ ] docker.md

---

# Phase 22 - Testing / Infrastructure

- [x] Backend feature tests foundation
- [x] Translation runtime tests
- [ ] API tests
- [ ] Auth tests
- [ ] RBAC tests
- [ ] Queue tests
- [ ] Realtime tests
- [ ] Frontend integration tests
- [x] Isolate Laravel tests in dedicated database

---

# Phase X - Shared Frontend Architecture

- [x] Shared UI component foundation
- [x] Shared overlay/modal system
- [x] Shared table architecture
- [x] Shared filters architecture
- [x] Shared pagination architecture
- [x] Shared translation architecture
- [x] Shared loading architecture
- [x] Shared realtime-aware rendering foundation

---

# Phase 23 - Final Polish

- [ ] Remove debug logs
- [ ] Cleanup architecture
- [ ] Naming consistency
- [ ] Review commit history
- [ ] Final UI cleanup
- [ ] Validate folder structure

---

# Phase 24 - RELEASE 🚀

- [ ] Full docker-compose test
- [ ] Backend validation
- [ ] Angular validation
- [ ] Vue validation
- [ ] Queue validation
- [ ] WebSocket validation
- [ ] Final commit
- [ ] Create release tag
- [ ] Publish repository

---

# Phase 25 - Version 2 Roadmap

- [ ] Multi-tenancy
- [ ] Advanced realtime analytics
- [ ] Service extraction
- [ ] Dedicated notification microservice
- [ ] Dedicated websocket/realtime service
- [ ] Dedicated auth service
- [ ] Horizontal scaling
- [ ] Kubernetes evaluation
- [ ] Monitoring stack
- [ ] Advanced CI/CD

---

# Notes

- Each step = separate commit
- Clean architecture only
- API-first approach
- No business logic inside controllers
- Shared API contract for Vue and Angular
- Realtime uses queue-based broadcasting
- Gradual migration instead of rewrite
- Modular monolith before microservices
- Foundation tasks marked as foundation are intentionally not final enterprise implementations

---

# Final Goal

Build a realistic SaaS platform demonstrating:

- Laravel API-first backend
- Vue admin panel
- Angular client dashboard
- RBAC architecture
- Queue-based async processing
- Event-driven architecture
- WebSocket realtime system
- Modular monolith architecture
- Future microservice scalability
- Docker infrastructure
- Production-ready engineering workflow


# Future Hardening

## WebSockets & Realtime

- [ ] Migrate system.notifications to private-only channel
- [ ] Add e2e browser WebSocket tests
- [ ] Add advanced reconnect/backoff strategy
- [ ] Improve presence UI
- [ ] Add activity stream pagination/backpressure
- [ ] Add production Reverb scaling

## Notifications System

- [ ] Add granular notification preferences by type/channel
- [ ] Add notification retention and cleanup policy
- [ ] Add notification templates
- [ ] Add email delivery integration
- [ ] Add e2e notification realtime browser tests
- [ ] Add notification domain analytics
- [ ] Migrate system.notifications smoke path to private-only when no longer needed