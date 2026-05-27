# Security

## Rate Limiting

The API uses named rate limiters to protect critical endpoints without changing API contracts.

### Endpoint groups and limiter names

- Auth login/token endpoints: `throttle:auth-login`
- API docs routes (`/docs/api*`): `throttle:api-docs`
- Chat message send: `throttle:chat-message-send`
- Chat typing start/stop: `throttle:chat-typing`
- Chat attachment upload: `throttle:chat-attachments`
- Chat external API endpoints: `throttle:chat-external-api`
- Chat webhook management routes: `throttle:chat-webhook-management`

### Environment keys

- `SECURITY_RATE_LIMITS_ENABLED`
- `AUTH_LOGIN_RATE_LIMIT_MAX_ATTEMPTS`
- `AUTH_LOGIN_RATE_LIMIT_DECAY_SECONDS`
- `API_DOCS_RATE_LIMIT_MAX_ATTEMPTS`
- `API_DOCS_RATE_LIMIT_DECAY_SECONDS`
- `CHAT_TYPING_RATE_LIMIT_MAX_ATTEMPTS`
- `CHAT_TYPING_RATE_LIMIT_DECAY_SECONDS`
- `CHAT_ATTACHMENT_RATE_LIMIT_MAX_ATTEMPTS`
- `CHAT_ATTACHMENT_RATE_LIMIT_DECAY_SECONDS`

Existing chat limiter keys remain in `chat.php`:

- `CHAT_MESSAGE_SEND_RATE_LIMIT_*`
- `CHAT_EXTERNAL_API_RATE_LIMIT_*`
- `CHAT_WEBHOOK_MANAGEMENT_RATE_LIMIT_*`

### Key strategy

- Auth login: `email + ip`, fallback to `ip`
- API docs: `user_id`, fallback to `ip`
- Chat typing: `user_id + conversation_id + ip`
- Chat attachments: `user_id + message_id + ip`
- Existing chat limiters keep their current key strategy.

### 429 behavior

Laravel returns `429 Too Many Requests` with a safe body. The application does not include password/token/secret values in limiter keys or response payloads.

### Safety rules

- Never log raw passwords, bearer tokens, webhook secrets, or authorization headers.
- Do not apply aggressive throttles to normal read/list endpoints.
- Keep high-frequency endpoints (typing) softly throttled to avoid UX regressions.

## Secure Headers

Security headers are applied by Laravel middleware (`SecurityHeadersMiddleware`) for web, API, and docs responses.

### Enabled headers

- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `X-Frame-Options: SAMEORIGIN`
- `X-Permitted-Cross-Domain-Policies: none`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`

### CSP policy

- Config key: `security.headers.content_security_policy.*`
- Default mode is enforced header (`Content-Security-Policy`).
- Optional report-only mode via `SECURITY_CSP_REPORT_ONLY=true`.
- Default policy is conservative and Swagger/dev compatible (includes inline/eval allowances for compatibility).

### HSTS policy

- Config key: `security.headers.hsts.*`
- Disabled by default (`SECURITY_HSTS_ENABLED=false`) for local/dev.
- Applied only when enabled and request is HTTPS.

### Local/dev behavior

- Headers can be disabled with `SECURITY_HEADERS_ENABLED=false` for targeted debugging.
- Docs access policy remains controlled by docs access middleware/gates; secure headers do not bypass permissions.

### Production recommendations

- Enable HSTS only behind HTTPS.
- Tighten CSP incrementally after validating Swagger UI and admin assets.
- Consider switching CSP to report-only first when introducing stricter directives.

### Verify with curl

- `curl -I http://localhost:8080/api/v1/health`
- `curl -I http://localhost:8080/docs/api/portal`

## Validation Hardening

Validation follows a FormRequest-first policy for critical API endpoints.

- Auth login/session login use dedicated FormRequest classes.
- Chat payloads validate enums/types and body length bounds.
- Participant and conversation create flows validate referenced user IDs.
- External API payloads validate provider/external ID/idempotency key format and max lengths.
- Webhook endpoint requests validate event allowlists, URL shape, and scoped arrays.
- Attachment upload validation enforces file type and max size from config.

Safe validation error behavior:

- API validation failures return standardized `422` JSON envelope.
- Validation responses do not include secrets/tokens/signatures/storage paths.
- Invalid webhook signature remains `403` with safe generic message.

Known gaps:

- Full SSRF hardening for webhook target URLs is tracked separately and is not part of this validation-only step.
