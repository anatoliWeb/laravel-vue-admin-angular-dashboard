# Docker

## Docker Image Optimization

### Build context policy

- Root build context is used by current compose services.
- `.dockerignore` is the primary guard to keep build context lean and safe.
- Never include runtime secrets (`.env*`) or local DB/Redis volume data in image build context.

### `.dockerignore` policy

Ignored classes include:

- VCS/editor artifacts (`.git`, `.idea`, `.vscode`)
- secrets (`.env*`, except `.env.example`)
- dependency folders (`node_modules`, `vendor`)
- runtime logs/cache/temp (`storage/logs`, `dist`, `build`, `.cache`, `tmp`, `*.log`)
- local Docker state (`docker/data/mysql`, `docker/data/redis`)

### Layer cache strategy

- PHP image uses a stable base (`php:8.3-fpm`) and minimizes install layers.
- APT install uses `--no-install-recommends`, then cleans package metadata/caches.
- Composer binary is copied from a pinned major image (`composer:2`) instead of floating latest.
- Queue/reverb/horizon reuse the same backend image in compose to avoid duplicate Dockerfile maintenance.

### Dependency install strategy

- Current local dev strategy uses bind mounts + runtime install checks for developer convenience.
- This is intentionally dev-first and avoids aggressive production-only multi-stage split in this phase.

### Dev vs production assumptions

- Local compose prioritizes fast iteration and mounted source.
- Production hardening can later introduce dedicated multi-stage targets for:
  - backend runtime-only image
  - frontend static build artifacts
  - stricter non-root runtime and minimized packages

### Production readiness checklist

- Keep `.env` out of images and CI artifacts.
- Use pinned image tags/digests in deployment pipelines.
- Build with `--no-cache` only when troubleshooting reproducibility.
- Review image size and layer diff before release.

### Useful commands

- `docker compose config`
- `docker compose build backend`
- `docker compose build frontend`
- `docker compose build nginx`
- `docker images`
- `docker system df`
- `docker build --no-cache -f docker/php/Dockerfile .`
