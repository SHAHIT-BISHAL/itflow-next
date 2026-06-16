# ITFlow-Next

A ground-up rebuild of ITFlow as a modern Laravel application — Livewire 3 + Tailwind CSS,
targeting a full MSP platform: IT documentation (IT Glue-style), modern ticketing with
email-to-ticket ingestion, CRM/Sales pipeline, and billing.

This repo does **not** contain a full Laravel project. Instead:

- `install.sh` — one-shot Ubuntu 24.04 installer. It installs PHP 8.3, MySQL, Nginx,
  Composer, Node.js, scaffolds a fresh Laravel 11 app, installs Breeze (Livewire stack),
  Tailwind, and `spatie/laravel-permission`, then copies `overlay/` on top and runs
  migrations + seeders.
- `overlay/` — our custom application code (migrations, models, Livewire components,
  views, routes, config, seeders, tests), laid out as a mirror of a Laravel project's
  directory structure. The installer copies this directly into the generated Laravel app.

## Quick start (Ubuntu 24.04)

```bash
sudo bash install.sh
```

Defaults can be overridden via environment variables, e.g.:

```bash
APP_DOMAIN=msp.example.com APP_DIR=/var/www/itflow-next sudo -E bash install.sh
```

After install:
- Visit `http://<APP_DOMAIN>`
- Log in with `admin@itflow-next.test` / `password` — **change this immediately**.
- DB credentials are written to `/root/itflow-next-db-credentials.txt`.

## Manual / local setup

If you already have PHP 8.3, Composer, Node, and MySQL installed:

```bash
composer create-project laravel/laravel itflow-next "^11.0"
cd itflow-next
composer require laravel/breeze livewire/livewire spatie/laravel-permission
php artisan breeze:install livewire
rsync -a ../overlay/ ./
composer install
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
cp .env.example .env   # then configure DB_* values
php artisan key:generate
php artisan migrate --seed
npm install && npm run dev   # or npm run build
php artisan serve
```

## Phase 1 scope (this commit)

- Multi-company-ready data model: `companies`, `clients`, `contacts`, `locations`,
  `tags`/`taggables`, `categories`, `custom_fields`/`custom_field_values`,
  `user_client_permissions`.
- Roles & permissions via `spatie/laravel-permission` (Administrator, Technician, Read Only).
- Two auth guards: `web` (staff users) and `client` (contacts — foundation only,
  full client portal lands in a later phase).
- App shell: sidebar + topbar + Tailwind component set (`x-ui.*`).
- CRUD: Clients (with favorites/search/archive), Contacts & Locations (nested under
  a client's detail page), Users & Roles (admin), Tags & Categories (admin).
- Dashboard with live counts.

See `CHANGELOG` / project plan for the full roadmap (IT documentation, ticketing +
email ingestion, CRM/sales, billing).

## Running tests

```bash
php artisan test
```
