# Monitoring

## Queue Logging

Queue logging is enabled by default and is focused on critical queue lifecycle visibility with safe structured context.

### Configuration

- `LOG_QUEUE_EVENTS=true|false`
- Runtime key: `config('logging.queue.enabled')`

### Logged lifecycle events (critical jobs)

For `DeliverChatWebhookJob`:

- `queue.webhooks.delivery.started`
- `queue.webhooks.delivery.completed`
- `queue.webhooks.delivery.retry_scheduled`
- `queue.webhooks.delivery.failed` / exception variants
- `queue.webhooks.delivery.cancelled`

### Safe context fields

- `job_class`
- `queue`
- `job_delivery_id`
- `delivery_id`
- `delivery_uuid`
- `webhook_endpoint_id`
- `event`
- `attempt`
- `max_tries`
- `status`
- `response_status`
- `duration_ms`
- `error_class` / `error_summary` (on failure)

### Never log

- `token`, `token_hash`
- `secret`, `webhook_secret`
- `signature`, `authorization`
- `raw_payload`, `raw_response`, full `payload`, `response_body`
- `device_key`, `user_agent`, `ip_address`

### Operational checks

- `php artisan queue:failed`
- `php artisan queue:retry all`
- `php artisan queue:restart`

Queue logging must remain signal-oriented and avoid noisy per-record payload dumps.
