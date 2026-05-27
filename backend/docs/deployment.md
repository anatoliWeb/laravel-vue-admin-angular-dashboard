# Deployment

## Production Configuration

The repository `docker-compose.yml` is development-oriented and should not be used as-is for production deployment.

Use:

- `backend/.env.production.example` as a template
- `docker-compose.prod.example.yml` as a baseline example

Both files are intentionally templates, not drop-in production manifests.

## Environment Variables

Minimum production defaults:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` must be generated (`php artisan key:generate --show`)
- `APP_URL=https://...`

Do not commit real secrets. Inject them at deploy time (secret manager, CI/CD variables, or host-level env files).

## Security Defaults

- `SECURITY_HEADERS_ENABLED=true`
- `SECURITY_HSTS_ENABLED=true` (only behind HTTPS)
- `SECURITY_CSP_ENABLED=true`
- `API_DOCS_LOCAL_BYPASS=false`

Session/cookie security:

- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax` (or `strict` if your UX allows it)

## Cache/Queue

Production baseline:

- `CACHE_STORE=redis`
- `QUEUE_CONNECTION=redis`
- `QUEUE_FAILED_DRIVER=database-uuids`

Queue workers should run under supervisor or an equivalent process manager and be restarted safely:

- `php artisan queue:restart`

## Logs

Container-friendly logging recommendation:

- `LOG_CHANNEL=stack`
- `LOG_STACK=stderr`
- `LOG_LEVEL=info`

This keeps logs in stdout/stderr for `docker logs` and external collectors.

## API Docs Access

OpenAPI docs should stay protected in production:

- `api.docs.view` required for portal/filtered docs routes
- `api.docs.view.full` required for raw `/docs/api` and `/docs/api.json`

Never enable local bypass in production:

- `API_DOCS_LOCAL_BYPASS=false`

## Reverb/WebSockets

Configure production websocket endpoints with TLS-aware values:

- `REVERB_HOST=realtime.example.com`
- `REVERB_PORT=443`
- `REVERB_SCHEME=https`
- frontend `VITE_REVERB_*` aligned to public realtime host

## Migrations and Releases

Run migrations explicitly during release:

- `php artisan migrate --force`

Recommended release order:

1. Pull new image/version
2. Run migrations
3. Restart app/queue workers
4. Run health checks

## Health Checks

Keep health endpoints enabled:

- `MONITORING_HEALTH_ENABLED=true`
- `MONITORING_HEALTH_PROTECTED_ENABLED=true`

Use `/health` for liveness and protected monitoring endpoint for readiness checks.

## Production Checklist

- Secrets injected externally (not committed)
- `APP_DEBUG=false`
- HTTPS reverse proxy in front of app
- HSTS enabled only with HTTPS
- Redis for cache/queue
- Queue workers supervised
- DB/Redis not publicly exposed
- Storage permissions validated (`storage`, `bootstrap/cache`)
- Backups configured (DB and critical storage)
