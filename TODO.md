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
- [x] Implement direct-to-group creation service
- [x] Implement history import service
- [x] Implement imported history audit logging
- [x] Add tests for direct-to-group history import

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
- [x] Implement participant restriction service
- [x] Implement block/unblock participant actions
- [x] Add participant access tests

---

## Device-level Read State

- [x] Define aggregated user-level read state
- [x] Define per-device read state
- [x] Define chat user devices table
- [x] Define message device reads table
- [ ] Register/update chat device from frontend
- [ ] Store stable device key on client
- [x] Track message read state per device
- [x] Sync aggregated user read state from device reads
- [ ] Show per-device read information in admin, якщо потрібно
- [x] Add device-level read tests

---

## Chat Backend

- [x] Chat API foundation
- [x] Conversation model
- [x] Conversation participants model
- [x] Message model
- [x] Message read state
- [x] Device-level message read state
- [x] Message delivery status
- [x] Chat user device model
- [x] Direct chats
- [x] Group chats
- [x] Public/private conversations
- [x] Conversation type support
  - [x] direct
  - [x] group
  - [x] support/admin
  - [x] external/API
  - [x] system
- [x] Conversation visibility support
  - [x] private
  - [x] public
- [x] Join policy support
  - [x] invite_only
  - [x] participants_can_invite
  - [x] anyone_with_permission
  - [x] public_join
- [x] Participant roles
  - [x] owner
  - [x] admin
  - [x] member
  - [x] viewer
  - [x] support
- [x] Participant capability checks
- [x] Participant access/blocking checks
- [x] Conversation permissions
- [x] Message ownership checks 
- [x] Message soft delete
- [x] Conversation archive/close state
- [x] Message search foundation
- [x] Chat backend tests

---

## Chat Database Tables

- [x] `conversations`
- [x] `conversation_participants`
- [x] `messages`
- [x] `message_reads`
- [x] `chat_user_devices`
- [x] `message_device_reads`
- [x] `message_deliveries`
- [x] `message_attachments`
- [x] `external_message_mappings`
- [x] `chat_webhook_endpoints`
- [x] `chat_webhook_deliveries`
- [x] `chat_moderation_logs`
- [x] `add_chat_message_references_to_conversations_table`
- [x] `add_chat_message_references_to_conversation_participants_table`
- [x] Review indexes before implementation
- [x] Review unique constraints before implementation
- [x] Review foreign keys before implementation
- [x] Review cascade/delete behavior before implementation
- [x] Run migrations locally
- [x] Run migrations in testing database
- [x] Verify migration rollback

---

## Chat Demo Seed Data

- [x] Create `ChatDemoSeeder`
- [x] Add `CHAT_DEMO_SEED` env flag
- [x] Add `CHAT_DEMO_MESSAGES_COUNT` env setting
- [x] Prevent fake chat seed in production
- [x] Seed demo conversations
  - [x] direct
  - [x] private group
  - [x] public group
  - [x] support/admin
  - [x] external/API
- [x] Seed 300+ demo messages
- [x] Seed message deliveries
- [x] Seed message reads
- [x] Seed device-level reads, 
- [x] Seed imported history example
- [x] Add safe cleanup for previous demo seed data

---

## Message Attachments

- [x] Message file attachments foundation
- [x] Attachment upload endpoint
- [x] Attachment download endpoint
- [x] Attachment preview metadata
- [x] Attachment ownership checks
- [x] Attachment size limits
- [x] Allowed MIME types
- [x] Storage disk configuration
- [x] Attachment status support
  - [x] active
  - [x] deleted
  - [x] quarantined
  - [x] failed
- [x] Imported/copied attachment support
- [x] Virus/security scan placeholder
- [x] Image/document/audio support strategy
- [x] Attachment cleanup on message delete
- [x] Attachment tests

---

## Chat API

- [x] List conversations API
- [x] Create direct conversation API
- [x] Create group conversation API
- [x] Create private group from direct conversation API
- [x] Import direct history into new group conversation API
- [x] Register/update chat device API
- [x] Mark message/conversation as read from device API
- [x] Add/remove participants API
- [x] Block/unblock participant API
- [x] Update participant access/capabilities API
- [x] Load messages API
- [x] Send message API
- [x] Edit message API, якщо потрібно
- [x] Delete message API
- [x] Mark conversation as read API
- [x] Upload message attachment API
- [x] List conversation participants API
- [x] Leave conversation API
- [x] Close/archive conversation API
- [x] API validation
- [x] API feature tests

---

## Realtime Chat

- [x] Realtime message created event
- [x] Realtime message updated event
- [x] Realtime message deleted event
- [x] Realtime message read event
- [x] Realtime device-level read event
- [x] Realtime delivery status event
- [x] Realtime participant access changed event
- [x] Private chat channels
- [x] Presence chat channels
- [x] Typing indicators
- [x] Online users state
- [x] Conversation presence state
- [x] User joined/left conversation event
- [x] Realtime attachment notification
- [x] Realtime tests
- [x] Queue-based realtime broadcasting

---

## Presence / Online Users

- [x] Use existing presence foundation from Phase 11
- [x] `presence-chat.{conversationId}`
- [ ] Show online users in chat sidebar
- [x] Show who is currently inside conversation
- [ ] Show typing users
- [x] Show participant online/offline state
- [x] Show last seen placeholder/foundation
- [x] Optional device-aware presence foundation
- [x] Presence payload safe fields only
  - [x] id
  - [x] name
  - [x] avatar, if available and safe
- [x] Presence cleanup on disconnect
- [x] Presence tests

---

## Typing Indicators

- [x] Typing start event
- [x] Typing stop event
- [x] Typing debounce/throttle
- [ ] Typing timeout fallback
- [ ] Typing indicator in direct chat
- [ ] Typing indicator in group chat
- [ ] Typing indicator privacy rules
- [x] Typing tests

---

## External Chat API & Webhooks

- [x] External API message sending
- [x] API access tokens/scopes for chat
- [x] External message id / idempotency support
- [x] Incoming webhook endpoint for external messages
- [x] Outgoing webhooks for message events
- [x] Webhook endpoint management
- [x] Webhook delivery logs
- [x] Webhook retry strategy
- [x] Webhook signature verification
- [x] Webhook secret rotation foundation
- [x] Webhook failure handling
- [x] Webhook replay protection
- [x] Message delivery/read status callbacks
- [x] External API rate limiting
- [x] External API tests

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

- [x] Angular chat module
- [x] Conversation list
- [x] Direct chat UI
- [x] Group chat UI foundation
- [x] Public/private conversation UI foundation
- [x] Message thread UI
- [x] Send message form
- [x] File attachment upload UI
- [x] Attachment preview/download UI
- [x] Message read state UI
- [x] Register/store stable chat device key
- [x] Send device key on read actions
- [x] Participant access notice UI
  - [x] read_only notice
  - [x] hidden state handling
  - [x] blocked notice
  - [x] show_read_only_history mode
- [x] Online users sidebar
- [x] Typing indicators
- [x] Realtime message updates
- [x] Realtime read/delivery updates
- [x] Conversation participants panel
- [x] Search/filter conversations
- [x] Empty/loading/error states
- [x] Angular build/tests

---

## Vue Admin Chat / Monitoring

- [x] Vue admin chat monitoring page
- [x] Admin conversation list
- [x] Admin conversation detail view
- [ ] Admin can reply to conversations
- [x] Admin can see more metadata than Angular users
- [x] Admin can see participant info
- [x] Admin can see participant access state/capabilities
- [ ] Admin can block/unblock participants
- [ ] Admin can set participant read-only mode
- [ ] Admin can hide chat from participant
- [ ] Admin can see message delivery/read state
- [ ] Admin can see per-device read state, якщо потрібно
- [ ] Admin can see imported history markers
- [x] Admin can see external API source, if message came from API
- [ ] Admin can see webhook delivery status
- [ ] Admin can view attachments
- [ ] Admin can moderate/delete messages, if permission allows
- [ ] Admin can close/archive conversations
- [~] Admin filters
  - [x] direct/group/support/external
  - [x] private/public
  - [ ] unread
  - [ ] assigned/unassigned
  - [ ] blocked/restricted participants
  - [ ] failed webhook delivery
  - [ ] imported messages
- [ ] Vue realtime chat updates
- [x] Vue build/tests <!-- build PASS; npm test script is not configured for backend Vue toolchain -->

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

- [x] `chat.view`
- [x] `chat.create`
- [x] `chat.send`
- [x] `chat.edit`
- [x] `chat.delete`
- [x] `chat.conversations.view`
- [x] `chat.conversations.create`
- [x] `chat.conversations.edit`
- [x] `chat.conversations.close`
- [x] `chat.conversations.archive`
- [x] `chat.conversations.delete`
- [x] `chat.participants.view`
- [x] `chat.participants.add`
- [x] `chat.participants.remove`
- [x] `chat.participants.manage`
- [x] `chat.attachments.view`
- [x] `chat.attachments.upload`
- [x] `chat.attachments.download`
- [x] `chat.attachments.delete`
- [x] `chat.admin.view`
- [x] `chat.admin.reply`
- [x] `chat.admin.moderate`
- [x] `chat.admin.delete_messages`
- [x] `chat.admin.close_conversations`
- [x] `chat.admin.view_metadata`
- [x] `chat.external_api.use`
- [x] `chat.external_api.manage`
- [x] `chat.external_api.view_logs`
- [x] `chat.webhooks.view`
- [x] `chat.webhooks.create`
- [x] `chat.webhooks.edit`
- [x] `chat.webhooks.delete`
- [x] `chat.webhooks.manage`
- [x] `chat.webhooks.view_deliveries`
- [x] `chat.webhooks.retry_deliveries`
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
- [x] Device key validation and ownership checks
- [ ] Admin access checks
- [ ] External API token scopes
- [ ] Webhook HMAC signatures
- [ ] Webhook replay protection
- [x] Message attachment access control
- [x] Attachment MIME validation
- [x] Attachment size validation
- [ ] Sensitive data policy for messages
- [ ] Safe realtime payloads
- [ ] Safe presence payloads
- [x] Safe device-level read payloads
- [x] Safe imported history visibility
- [ ] Rate limiting for message sending
- [ ] Rate limiting for external API
- [ ] Abuse/spam protection placeholder

---

## Tests

- [x] Migration tests / migrate:fresh
- [x] Seeder tests / local smoke check
- [x] Conversation API tests
- [x] Message API tests
- [ ] Direct-to-group history import tests
- [ ] Group chat tests
- [x] Participant access tests
- [x] Participant blocking/read-only/hidden tests
- [x] Message read state tests
- [x] Device-level read state tests
- [ ] Message delivery state tests
- [x] Realtime message tests
- [ ] Presence channel tests
- [ ] Typing indicator tests
- [x] Attachment upload/download tests
- [ ] External API tests
- [ ] Webhook delivery tests
- [ ] Webhook signature tests
- [ ] Admin monitoring tests
- [ ] RBAC tests
- [x] Full backend suite
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