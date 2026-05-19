# Laravel + Vue Admin + Angular Dashboard SaaS

## Overview

This project represents a production-grade SaaS architecture built as a portfolio system.

It demonstrates how to design a scalable system where:

* Laravel acts as a centralized API backend
* Vue is used for internal admin panel (system management)
* Angular is used for client-facing dashboard
* All layers are connected through a unified API and RBAC system

The goal is not to build “just another CRUD app”, but to showcase real-world system design decisions:

* API-first backend
* Multi-frontend architecture
* Role-based access control across different clients
* Event-driven backend with queues and real-time updates

---

## Architecture

### High-level

```txt
Laravel (API + Core Logic)
        ↓
 ┌───────────────┬───────────────┐
 │ Vue Admin     │ Angular App   │
 │ (internal)    │ (clients)     │
 └───────────────┴───────────────┘
```

### Responsibilities

| Layer   | Responsibility                    |
| ------- | --------------------------------- |
| Laravel | Business logic, API, RBAC, events |
| Vue     | Admin panel (system control)      |
| Angular | Client dashboard (user-facing UI) |
| Redis   | Queues, cache, realtime           |
| MySQL   | Data storage                      |

---

## Why this architecture

### Laravel (Backend)

Used as the central brain of the system:

* clean service-based architecture
* strong validation and conventions
* scalable API-first approach

### Vue (Admin Panel)

Chosen for:

* tight integration with Laravel (Vite)
* fast UI development
* internal system management

### Angular (Client Dashboard)

Chosen for:

* structured enterprise-grade frontend
* separation from admin logic
* scalability for user-facing features

### RBAC System

A unified permission system controls access across:

* admin panel (Vue)
* dashboard (Angular)
* API endpoints (Laravel)

This ensures consistent behavior across all layers.

---

## Features

### Backend (Laravel)

* API-first architecture
* Service layer (clean business logic separation)
* RBAC (roles + permissions + overrides)
* Activity logging system
* Queue system (Redis)
* Event-driven architecture
* WebSocket broadcasting (real-time)
* Token-based authentication (Sanctum)

---

### Admin Panel (Vue)

* Runs inside Laravel (Vite)
* Role-based UI rendering
* System management (users, roles, permissions)
* Activity monitoring

---

### Client Dashboard (Angular)

* Separate frontend (Docker container)
* Authenticated API consumption
* User-specific data
* Real-time updates
* Dashboard widgets (stats, activity)

---

### Infrastructure

* Docker Compose (full environment)
* Nginx
* PHP-FPM
* MySQL 8
* Redis 7
* Queue worker (Supervisor)

---

## Tech Stack

### Backend

* PHP 8+
* Laravel
* Sanctum (auth)
* Redis (queues, cache)
* Reverb (WebSockets)

### Frontend

* Vue 3 (admin)
* Angular (dashboard)

### Infrastructure

* Docker
* Nginx
* MySQL
* Redis

---

## Project Structure

```txt
/backend
  app/
  resources/js (Vue admin)
  routes/api.php

/frontend
  Angular dashboard

/docker
  nginx/
  php/
  supervisor/

docker-compose.yml
.env
TODO.md
docs/
```

---

## Running the Project

### 1. Clone repository

```bash
git clone <repository_url>
cd laravel-vue-admin-angular-dashboard
```

### 2. Setup environment

```bash
cp .env.example .env
```

### 3. Start containers

```bash
docker compose up -d
```

### 4. Access applications

* Backend API:
  [http://localhost:${APP_PORT}](http://localhost:${APP_PORT})

* Angular Dashboard:
  [http://localhost:${FRONT_PORT}](http://localhost:${FRONT_PORT})

---

## Development

### Backend

```bash
docker compose exec backend php artisan migrate
docker compose exec backend php artisan queue:work
```

### Testing database

Laravel tests are isolated from the main dev database and use a dedicated DB:

- `DB_CONNECTION=mysql`
- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_DATABASE=saas_testing`
- `DB_USERNAME/DB_PASSWORD` from `backend/.env.testing`
- root env defaults for fresh setup:
  - `TEST_DB_DATABASE=saas_testing`
  - `TEST_DB_USERNAME=saas`
  - `TEST_DB_PASSWORD=secret`

Create testing DB once:

```bash
docker compose exec mysql mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS saas_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
docker compose exec mysql mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON saas_testing.* TO 'saas'@'%'; FLUSH PRIVILEGES;"
```

Run tests:

```bash
docker compose exec backend php artisan test
docker compose exec backend php artisan test --filter=RealtimeChannelAuthorizationTest
```

Important:
- Do not run tests against the main dev DB (`saas`).
- RefreshDatabase/migrations are expected to reset only `saas_testing`.
- Run tests sequentially (`php artisan test`) against a single MySQL testing DB.
- Do not run multiple `php artisan test` processes in parallel unless separate parallel test databases are configured.
- To verify active test DB quickly, run:
  - `docker compose exec backend php artisan test --filter=RealtimeChannelAuthorizationTest`
  - and ensure safety guard in `backend/tests/TestCase.php` does not fail.

---

### Vue Admin (inside Laravel)

```bash
docker compose exec backend npm run dev
```

---

### Angular Dashboard

```bash
docker compose exec frontend npm run start
```

---

## Environment

All configuration is centralized in:

```txt
.env
```

This file controls:

* ports
* database credentials
* Redis
* WebSockets
* frontend API URLs

Important:

* `.env.example` is committed
* `.env` is local only
* changing `.env` requires container restart

---

## Realtime Architecture

```txt
Event → Queue → Broadcast → WebSocket → Frontend
```

Used for:

* live updates
* notifications
* dashboard refresh
* chat (optional extension)

---

## Development Approach

* API-first design
* Service-oriented backend
* Feature-based commits
* Clean architecture (no business logic in controllers)
* Shared API contract between Vue and Angular

Migration policy:
- Before production/release, existing migrations may be edited if schema is still not finalized.
- After production/release, schema changes should be made only via new migrations.

---

## TODO & Roadmap

See:

```txt
TODO.md
```

Includes:

* authentication
* RBAC
* queues
* events
* realtime
* dashboard features
* final release

---

## Final Goal

This project demonstrates:

* Multi-frontend architecture (Vue + Angular)
* Centralized backend (Laravel)
* RBAC across multiple clients
* Queue-based async processing
* Event-driven system
* Real-time updates (WebSockets)
* Docker-based environment
* Production-ready structure

---

## License

MIT
