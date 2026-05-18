# Queue Operations Runbook

This runbook describes minimal operational commands for Laravel queue workers and failed jobs.

## Scope

- Backend queue driver: `redis`
- Queue worker container: `saas_queue_worker`
- Failed jobs storage: `failed_jobs` table

## Check Queue Worker Status

```bash
docker compose ps queue-worker
docker compose logs -f queue-worker
```

## Quick Queue Diagnostics Baseline

Run compact diagnostics command:

```bash
docker compose exec backend php artisan system:queue-status
```

It reports:

- queue connection driver
- failed jobs count
- Redis status (when queue driver is redis)
- queue worker logs hint

## Inspect Failed Jobs

List failed jobs:

```bash
docker compose exec backend php artisan queue:failed
```

## Retry Failed Jobs

Retry a single failed job by UUID:

```bash
docker compose exec backend php artisan queue:retry <failed-job-uuid>
```

Retry all failed jobs:

```bash
docker compose exec backend php artisan queue:retry all
```

## Forget / Delete Failed Jobs

Delete one failed job by UUID:

```bash
docker compose exec backend php artisan queue:forget <failed-job-uuid>
```

Delete all failed jobs:

```bash
docker compose exec backend php artisan queue:flush
```

## Queue Worker Runtime Settings (Current)

Worker command is managed by Supervisor:

```text
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

Config source:

- `docker/supervisor/supervisord.conf`
- `backend/docker/queue/entrypoint.sh`

## Job-Level Retry Policy (Activity)

`LogActivityJob` defines explicit retry policy:

- `tries = 3`
- `timeout = 60`
- `backoff = [10, 30, 60]`

This policy is aligned with worker-level guardrails and keeps retry behavior explicit at job level.

## Notes

- `queue:flush` is destructive and should be used only when failed payloads are no longer needed.
- Retry commands should be executed after root-cause investigation to avoid repeated failures.
- This runbook does not introduce Horizon and keeps current queue architecture unchanged.
