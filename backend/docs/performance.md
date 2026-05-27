# Performance

## Redis Caching

This project uses Laravel cache store abstraction with Redis as the recommended backend.

### What Is Cached
- Meta bootstrap payload (`/api/v1/meta/bootstrap`) with user-scoped key.
- RBAC payload sections (`roles`, `permissions`, `role_permissions`) with RBAC versioned keys.
- Effective user permissions with user + global RBAC versioned keys.
- Dashboard stats summary (`/api/v1/stats`) with short TTL.
- Filtered OpenAPI spec (`/docs/api.filtered.json`) with user + RBAC versioned key.

### What Is Not Cached
- Chat message lists and mutable conversation streams.
- Realtime/presence states.
- Tokens, secrets, signatures, authorization headers.
- Raw webhook payload/response bodies.

### Key Strategy
- `meta:bootstrap:user:{userId}:v{rbacVersion}:{userVersion}`
- `meta:rbac:roles:v{rbacVersion}`
- `meta:rbac:permissions:v{rbacVersion}`
- `meta:rbac:role_permissions:v{rbacVersion}`
- `rbac:user:{userId}:effective_permissions:v{globalVersion}:{userVersion}`
- `stats:dashboard:summary:v1`
- `docs:openapi:filtered:user:{userId}:full:{0|1}:rbac:{rbacVersion}:userv:{userVersion}`

### TTLs
Configured in `config/performance.php`:
- `PERFORMANCE_CACHE_DEFAULT_TTL`
- `PERFORMANCE_CACHE_META_TTL`
- `PERFORMANCE_CACHE_RBAC_TTL`
- `PERFORMANCE_CACHE_STATS_TTL`
- `PERFORMANCE_CACHE_API_DOCS_TTL`

### Invalidation
- RBAC changes: bump global RBAC cache version (no `Cache::flush()`).
- User role/permission changes: bump user bootstrap version + user permission version.
- Filtered docs cache naturally rotates with RBAC/user version changes.

### Safety Rules
- Never cache global copies of user-specific permission responses.
- Never cache sensitive fields: token, secret, authorization, webhook secrets, device keys, raw payloads.
- Keep mutable chat runtime endpoints uncached unless explicit bottleneck and strict invalidation plan exist.

### Local Commands
- `php artisan cache:clear`
- `php artisan optimize:clear`
- `docker compose exec redis redis-cli -a "$REDIS_PASSWORD" ping`

