# OpenAPI Preparation

## Goals
- Prepare a stable inventory for future OpenAPI generator integration.
- Capture current auth/middleware/validation/resource contracts without changing API behavior.
- Identify known gaps before selecting a generator package.

## Current API foundations
- Pagination foundation: available (`meta.current_page`, `meta.last_page`, `meta.per_page`, `meta.total`).
- Filtering/sorting/search foundation: available on multiple list endpoints (notably chat message search and conversation lists).
- Validation standardization: API validation errors return unified envelope (`success=false`, `message`, `errors`).
- Response envelope baseline: success/error/meta contract via `BaseController` + `ApiResponse` + API exception rendering.

## Auth schemes
- `auth:sanctum` for protected `/api/v1/*`.
- Session auth endpoints:
  - `POST /api/v1/auth/session/login`
  - `GET /api/v1/auth/session/me`
  - `POST /api/v1/auth/session/logout`
- Token/Bearer endpoints:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/token`
  - `GET /api/v1/auth/me`
  - `POST /api/v1/auth/logout`
- External chat token scopes middleware:
  - `external.chat.scope:chat.external.messages.send`
- Rate limiters in route middleware:
  - `throttle:chat-message-send`
  - `throttle:chat-external-api`
  - `throttle:chat-webhook-management`
- Webhook security:
  - HMAC signature verification
  - timestamp tolerance
  - replay protection
  - secret rotation support

## Response envelope
- Success:
  - `success: true`
  - `message: string`
  - `data: mixed`
  - `meta?: object`
- Error:
  - `success: false`
  - `message: string`
  - `errors: object|array`
  - `meta?: object`

## Common Response Envelope

### 1. Success response
```json
{
  "success": true,
  "message": "Request successful",
  "data": {},
  "meta": {}
}
```

### 2. List/paginated response
```json
{
  "success": true,
  "message": "Data fetched",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 7,
    "per_page": 15,
    "total": 100
  }
}
```

### 3. Error response
```json
{
  "success": false,
  "message": "Request failed",
  "errors": {}
}
```

### 4. Validation error response
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "field": [
      "The field is required."
    ]
  }
}
```

### 5. Common HTTP error variants
- `401 Unauthenticated`:
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": []
}
```
- `403 Forbidden`:
```json
{
  "success": false,
  "message": "Forbidden",
  "errors": []
}
```
- `404 Not Found`:
```json
{
  "success": false,
  "message": "Resource not found",
  "errors": []
}
```
- `429 Too Many Requests`:
```json
{
  "success": false,
  "message": "Too Many Attempts.",
  "errors": []
}
```
- `422 Validation`:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": [
      "Validation message"
    ]
  }
}
```
- `500 Server Error`:
```json
{
  "success": false,
  "message": "Server error",
  "errors": []
}
```

OpenAPI schema candidates for this contract are registered in generated docs as:
- `ApiSuccessResponse`
- `ApiErrorResponse`
- `ValidationErrorResponse`
- `PaginatedResponse`
- `PaginationMeta`

## Error responses
- Validation: `422` + `message=Validation failed` + field-level `errors`.
- Unauthenticated: `401` + `message=Unauthenticated`.
- Forbidden: `403` + `message=Forbidden`.
- Not found: `404` + standardized JSON envelope.

## Pagination / filtering / sorting / search
- Pagination meta envelope already standardized across API list endpoints.
- Chat search endpoint:
  - `GET /api/v1/chat/conversations/{conversation}/messages/search`
  - uses `SearchChatMessagesRequest`
  - supports search-oriented params + pagination.
- Conversation/message lists support filter/sort foundations through query services.

## Route inventory (critical groups)

| Route | Method | Controller | Auth | Permission / Scope | Request class | Resource/Response | Pagination | Filters/Sort/Search | Notes |
|---|---|---|---|---|---|---|---|---|---|
| `/api/v1/auth/login` | POST | `AuthController@token` | public | - | inline `Request` | envelope | no | no | token login |
| `/api/v1/auth/session/login` | POST | `AuthController@sessionLogin` | `web` | - | inline `Request` | envelope | no | no | session-first flow |
| `/api/v1/auth/me` | GET | `AuthController@me` | sanctum | - | - | `UserResource` envelope | no | no | protected identity |
| `/api/v1/meta/bootstrap` | GET | `MetaController@bootstrap` | sanctum | - | - | `MetaResource` envelope | no | no | lightweight bootstrap payload |
| `/api/v1/meta/rbac` | GET | `MetaController@rbac` | sanctum | - | - | `MetaResource` envelope | no | no | RBAC payload |
| `/api/v1/notifications/unread-count` | GET | `NotificationController@unreadCount` | sanctum | `notifications.view` | - | envelope | no | no | topbar counter |
| `/api/v1/chat/conversations` | GET | `ChatConversationController@index` | sanctum | `chat.view\|chat.conversations.view` | - | `ChatConversationResource` envelope | yes | filter/sort | chat list |
| `/api/v1/chat/conversations/{conversation}/messages` | GET | `ChatConversationController@messages` | sanctum | `chat.view\|chat.conversations.view` | - | `ChatMessageResource` envelope | yes | pagination | message list |
| `/api/v1/chat/conversations/{conversation}/messages` | POST | `ChatMessageController@store` | sanctum | `chat.send` + `throttle:chat-message-send` | `SendChatMessageRequest` | `ChatMessageResource` envelope | no | no | message send |
| `/api/v1/chat/messages/{message}` | PATCH | `ChatMessageController@update` | sanctum | `chat.edit\|chat.admin.moderate` | `UpdateChatMessageRequest` | `ChatMessageResource` envelope | no | no | message edit |
| `/api/v1/chat/messages/{message}` | DELETE | `ChatMessageController@destroy` | sanctum | `chat.delete\|chat.admin.delete_messages\|chat.admin.moderate` | - | envelope | no | no | soft-delete flow |
| `/api/v1/chat/external/messages` | POST | `ChatMessageController@storeExternal` | external token / user | `external.chat.scope:chat.external.messages.send` + `throttle:chat-external-api` | `SendExternalChatMessageRequest` | envelope + `meta.idempotent` | no | no | external API |
| `/api/v1/chat/external/webhooks/{endpoint:uuid}` | POST | `ChatIncomingWebhookController@handle` | public | `throttle:chat-external-api` | `IncomingChatWebhookRequest` | `ChatMessageResource` envelope | no | no | HMAC + replay protected |
| `/api/v1/chat/webhook-endpoints` | GET/POST | `ChatWebhookEndpointController@index/store` | sanctum | webhook permissions + management throttle | store: `StoreChatWebhookEndpointRequest` | `ChatWebhookEndpointResource` envelope | no | no | webhook management |
| `/api/v1/chat/conversations/{conversation}/webhook-deliveries` | GET | `ChatConversationController@webhookDeliveries` | sanctum | webhook view/manage/admin metadata | - | `ChatWebhookDeliverySummaryResource` envelope | yes | pagination | delivery status |

## Schema candidates
- `ApiSuccessResponse`
- `ApiErrorResponse`
- `ValidationErrorResponse`
- `PaginationMeta`
- `User`
- `Role`
- `Permission`
- `ChatConversation`
- `ChatMessage`
- `ChatAttachment`
- `ChatParticipant`
- `ChatDevice`
- `ChatReadState`
- `ChatWebhookEndpoint`
- `ChatWebhookDeliverySummary`
- `ExternalMessageRequest`
- `IncomingWebhookRequest`

## Known gaps before generator
- Legacy non-versioned `/api/*` routes still coexist with `/api/v1/*`; generator scope should prioritize `/api/v1/*`.
- Some controllers build paginated payload arrays inline instead of always calling `BaseController::paginatedResponse`.
- Not every endpoint uses dedicated `FormRequest` (some still validate inline `Request`).
- No selected OpenAPI package/annotations yet (this document is preparation only).

## Next step: API documentation generator
- Choose generator strategy (attributes/annotations vs route-introspection).
- Start with `/api/v1/*` only.
- Reuse schema candidates and route inventory from this document as source-of-truth.

## Swagger UI Try it out
- Docs UI: `/docs/api`
- OpenAPI JSON: `/docs/api.json`
- In local/testing, docs are accessible. In non-local environments, access is protected by `ApiDocsAccessMiddleware` and `Gate::allows('viewApiDocs')`.
- `Try it out` is enabled in Scramble UI config.

### Authorization flows in Swagger UI
- `BearerAuth`:
  - Use for protected `/api/v1/*` routes that require `auth:sanctum`.
  - In Swagger UI click `Authorize`, select `BearerAuth`, paste token value (without `Bearer` prefix).
- `SanctumSession`:
  - Documented as cookie auth (`laravel_session`) for session-first flows.
  - Practical Try-it usage in local is usually easier with bearer token unless browser session cookie is already established.
- `ExternalChatToken`:
  - Use for external chat routes such as `/api/v1/chat/external/messages`.
  - Token must have required scopes (for example `chat.external.messages.send`).
- `WebhookSignature` + `WebhookTimestamp`:
  - Documented for incoming webhook endpoint headers.
  - Signature and timestamp generation follows configured HMAC/tolerance rules.

### Security notes
- Do not place real production tokens/secrets in shared docs screenshots or examples.
- Docs/spec must not expose runtime secrets (`token_hash`, webhook secret values, raw signatures).

## API Docs Access Control
- Local/testing environments: `/docs/api` and `/docs/api.json` are available for developer workflow.
- Non-local environments: docs are protected by `ApiDocsAccessMiddleware`.
- Required permission: `api.docs.view` (checked by `Gate::allows('viewApiDocs')`).
- Recommended roles: `admin` and `developer` (if `developer` role exists in deployment RBAC setup).
- Do not expose docs routes publicly in production.
