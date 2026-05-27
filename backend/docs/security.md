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
