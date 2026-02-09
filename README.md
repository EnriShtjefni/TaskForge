# TaskForge

A mini-SaaS platform for project and task management: **organizations** have **projects**, projects have **tasks** and **comments**. Users have roles (owner, manager, member). Built with a Laravel API (Sanctum, Reverb) and a Nuxt 3 frontend (Pinia, Kanban board, real-time updates).

## Table of contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Quick start](#quick-start)
- [Running the application](#running-the-application)
- [Automatic testing](#automatic-testing)
- [Project structure](#project-structure)

---

## Overview

- **Goal**: Build a mini-SaaS platform for project and task management.
- **Domain**:
  - An **Organization** can have many **Projects**.
  - A **Project** can have many **Tasks** and **Comments** (via tasks).
  - **Users** belong to organizations via a many-to-many relation with a **role** on the pivot: `owner`, `manager`, `member`.
- **Authorization model** (enforced via Laravel Policies and Form Requests):
  - **Creates organization**: any authenticated **User**.
  - **Deletes project**: organization **Owner**.
  - **Creates task**: organization **Owner / Manager** of the project’s organization.
  - **Changes task status**: **assigned user** of that task.
  - **Comments on task**: any **project member** (user attached to the project).
- **Task status workflow**:
  - Allowed statuses: `todo`, `in_progress`, `review`, `done`.
  - Only valid transitions are accepted (both frontend and backend validate):  
    - `todo` → `in_progress`  
    - `in_progress` → `review`  
    - `review` → `done` or back to `in_progress`  
    - `done` → (no further transitions)
- **Main screens & navigation**:
  - **Auth**: `/login`, `/register` — Laravel Sanctum cookie-based auth for the Nuxt SPA.
  - **Dashboard**: `/dashboard` — summary cards and quick links to Organizations, Tasks (Kanban), and Activity Logs.
  - **Organizations & Projects**: `/organizations` — list organizations you belong to, manage projects inside each organization, manage project members (respecting roles).
  - **Task Board (Kanban)**: `/tasks` — select a project, see tasks grouped by status, drag & drop (valid transitions only), filters/search/pagination, assign users (only members), comments per task, real-time updates via Reverb.
  - **Activity Logs**: `/activity-logs` — read-only table of recent actions (auth + CRUD on organizations/projects/tasks/comments).

---

## Prerequisites

- **PHP** 8.5
- **Laravel** 12
- **Composer**
- **Node.js** 18+ and npm (or pnpm/yarn)
- **MySQL** 8+ (or SQLite for a quick local setup)
- **(Optional)** For real-time: Reverb runs on PHP; no extra runtime needed

---

## Quick start

### 1. Clone the repository

```bash
git clone git@github.com:EnriShtjefni/TaskForge.git
cd taskforge
```

### 2. Backend (Laravel API)

```bash
cd backend
```

1. **Copy environment file and configure**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` and set at least:

   - **`APP_KEY`** — generate with `php artisan key:generate` (see below).
   - **Database**: either SQLite or MySQL.
     - **SQLite**: `DB_CONNECTION=sqlite`, and ensure `database/database.sqlite` exists (created by default or `touch database/database.sqlite`).
     - **MySQL**: `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
   - **`APP_URL`** — URL of the API (e.g. `http://localhost:8000` or `http://localhost:8001`). The frontend will call this; keep it consistent with how you run `php artisan serve`.
   - **Sanctum (SPA auth)** — `SANCTUM_STATEFUL_DOMAINS` must include the frontend origin, e.g. `localhost:3000`.
   - **Reverb (real-time)** — if you use broadcasting:
     - `BROADCAST_CONNECTION=reverb`
     - `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET` — from `php artisan reverb:install` or your Reverb config.
     - `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME` (e.g. `localhost`, `8080`, `http`).
     - `VITE_REVERB_*` — same values so the frontend can connect (Nuxt reads these from env).

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Generate application key**

   ```bash
   php artisan key:generate
   ```

4. **Run migrations**

   ```bash
   php artisan migrate
   ```

5. **(Optional) Install Reverb and generate app credentials**

   If you want real-time task updates:

   ```bash
   php artisan reverb:install
   ```

   Then in `.env` set `BROADCAST_CONNECTION=reverb` and the generated `REVERB_APP_*` / `REVERB_HOST` / `REVERB_PORT` / `REVERB_SCHEME`. Copy the same key/host/port/scheme into `VITE_REVERB_*` so the frontend can connect.

---

### 3. Frontend (Nuxt 3)

```bash
cd frontend
```

1. **Copy environment file and configure**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` and set:

   - **`BACKEND_URL`** — full URL of the Laravel API (e.g. `http://localhost:8000`). Must match the backend `APP_URL` and the URL you use to run `php artisan serve`.
   - **Reverb (optional)** — if you use real-time, set `VITE_REVERB_APP_KEY`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT`, `VITE_REVERB_SCHEME` to the same values as in the backend `.env`.

2. **Install Node dependencies**

   ```bash
   npm install
   ```

   (Or `pnpm install` / `yarn` if you use those.)

---

## Running the application

You need the **API** and the **frontend** running. For **real-time updates** you also need **Reverb** and the **queue worker**.

### Terminal 1 — Laravel API

```bash
cd backend
php artisan serve
```


### Terminal 2 — Nuxt frontend

```bash
cd frontend
npm run dev
```

Open the app at the URL shown (usually `http://localhost:3000`). Log in or register; the frontend uses Laravel Sanctum (cookie-based auth) against the backend.

### (Optional) Real-time — Reverb WebSocket server

If you use task broadcasting (create/update/delete tasks and see updates without refresh):

```bash
cd backend
php artisan reverb:start
```

Keep this running. The frontend connects to Reverb using `VITE_REVERB_*` from `.env`.

### (Optional) Real-time — Queue worker

Task broadcast events are queued by default. Process the queue so events are actually sent to Reverb:

```bash
cd backend
php artisan queue:work
```

Keep this running. If you don’t run the queue worker, API calls (create/update/delete task) will succeed but other clients won’t receive real-time updates.


### Automatic Testing

To run the Laravel API tests:

```bash
cd backend
php artisan test
```

---

## Project structure

- **`backend/`** — Laravel API: auth (Sanctum), organizations, projects, tasks, comments, policies, Reverb broadcasting, queue.
- **`frontend/`** — Nuxt 3 SPA: login/register, dashboard, organizations & projects, task board (Kanban), real-time via Laravel Echo + Reverb.

