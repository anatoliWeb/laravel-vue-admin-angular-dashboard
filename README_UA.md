# Laravel + Vue Admin + Angular Dashboard SaaS

## Опис

Цей проєкт демонструє архітектуру production-рівня для SaaS системи.

Він побудований для портфоліо та показує, як спроєктувати масштабовану систему, де:

* Laravel виступає як центральний API backend
* Vue використовується для адмін-панелі (керування системою)
* Angular використовується для клієнтського dashboard
* Усі частини працюють через єдиний API та RBAC систему

Це не просто CRUD-додаток, а приклад реальних інженерних рішень:

* API-first підхід
* multi-frontend архітектура
* централізований контроль доступу
* event-driven backend з чергами та realtime

---

## Архітектура

### Загальна схема

```txt
Laravel (API + бізнес-логіка)
        ↓
 ┌───────────────┬───────────────┐
 │ Vue Admin     │ Angular App   │
 │ (адмінка)     │ (клієнти)     │
 └───────────────┴───────────────┘
```

### Відповідальність шарів

| Шар     | Відповідальність                |
| ------- | ------------------------------- |
| Laravel | API, бізнес-логіка, RBAC, події |
| Vue     | Адмін-панель                    |
| Angular | Користувацький dashboard        |
| Redis   | Черги, кеш, realtime            |
| MySQL   | Дані                            |

---

## Чому така архітектура

### Laravel (Backend)

Центральний “мозок” системи:

* чітка структура (Controller → Service → Model)
* сильна валідація
* API-first підхід

---

### Vue (Адмінка)

Використовується для:

* швидкої розробки інтерфейсу
* інтеграції з Laravel (Vite)
* керування системою (users, roles, permissions)

---

### Angular (Dashboard)

Використовується для:

* масштабованого клієнтського інтерфейсу
* розділення адмінки і користувацької частини
* складніших UI сценаріїв

---

### RBAC

Система ролей і прав доступу працює одночасно для:

* адмінки (Vue)
* dashboard (Angular)
* backend API (Laravel)

Backend є єдиним джерелом істини.

---

## Функціонал

### Backend (Laravel)

* API-first архітектура
* Service layer (бізнес-логіка)
* RBAC (roles + permissions)
* Activity logging (аудит)
* Черги (Redis)
* Події (Events)
* WebSockets (realtime)
* Token-based auth (Sanctum)

---

### Адмінка (Vue)

* Працює всередині Laravel
* Керування системою
* Контроль доступу
* UI залежить від permissions

---

### Dashboard (Angular)

* Окремий frontend (Docker)
* Робота через API
* Дані користувача
* Realtime оновлення

---

### Інфраструктура

* Docker Compose
* Nginx
* PHP-FPM
* MySQL 8
* Redis 7
* Queue worker (Supervisor)

---

## Стек

### Backend

* PHP 8+
* Laravel
* Sanctum
* Redis
* Reverb (WebSockets)

### Frontend

* Vue 3 (адмінка)
* Angular (dashboard)

### Infrastructure

* Docker
* Nginx
* MySQL
* Redis

---

## Структура проєкту

```txt
/backend
  app/
  resources/js (Vue адмінка)
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

## Запуск проєкту

### 1. Клонування

```bash
git clone <repository_url>
cd laravel-vue-admin-angular-dashboard
```

### 2. Налаштування

```bash
cp .env.example .env
```

### 3. Запуск

```bash
docker compose up -d
```

---

### Доступ

* Backend API:
  [http://localhost:${APP_PORT}](http://localhost:${APP_PORT})

* Angular Dashboard:
  [http://localhost:${FRONT_PORT}](http://localhost:${FRONT_PORT})

---

## Розробка

### Backend

```bash
docker compose exec backend php artisan migrate
docker compose exec backend php artisan queue:work
```

---

### Vue (адмінка)

```bash
docker compose exec backend npm run dev
```

---

### Angular

```bash
docker compose exec frontend npm run start
```

---

## Конфігурація

Вся конфігурація зберігається в:

```txt
.env
```

Це єдине джерело істини для:

* портів
* БД
* Redis
* WebSockets
* API URL

---

## Realtime архітектура

```txt
Event → Queue → Broadcast → WebSocket → Frontend
```

Використовується для:

* live оновлень
* нотифікацій
* dashboard
* чату (опціонально)

---

## Підхід до розробки

* API-first
* чиста архітектура
* сервісний шар
* відсутність логіки в контролерах
* один API для двох фронтів
* RBAC як основа системи

---

## TODO

Детальний план:

```txt
TODO.md
```

---

## Фінальна ціль

Проєкт демонструє:

* multi-frontend архітектуру (Vue + Angular)
* централізований backend (Laravel)
* RBAC систему
* черги та асинхронну обробку
* event-driven підхід
* realtime через WebSockets
* Docker інфраструктуру
* production-ready код

---

## Ліцензія

MIT
