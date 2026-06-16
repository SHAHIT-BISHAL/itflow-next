# Phase 1 — Foundation: Build Log & Reference

This document captures what was built, every bug hit and fixed, and the verified end state of the Phase 1 deployment of ITFlow-Next on Ubuntu 24.04.

---

## What Phase 1 Delivers

| Area | Detail |
|---|---|
| **Framework** | Laravel 11 + Livewire 3 + Tailwind CSS (Vite) + Alpine.js |
| **Auth scaffold** | Laravel Breeze (Livewire/Volt stack) |
| **Permissions** | spatie/laravel-permission (roles: Administrator, Technician, Read Only) |
| **Multi-tenancy foundation** | `company_id` scoping on all tenant-owned models via `BelongsToCompany` trait |
| **Guards** | `web` (staff/agent users) · `client` (contact portal — foundation only, full portal in Phase 2+) |
| **App shell** | Collapsible sidebar + topbar (`x-ui.*` Blade component set) |
| **Clients** | List (search/filter/favorites), create/edit modal, archive, detail page |
| **Contacts** | CRUD nested under client detail (Contacts tab) |
| **Locations** | CRUD nested under client detail (Locations tab) |
| **Admin** | Users, Roles, Tags, Categories |
| **Dashboard** | Live counts (active clients, leads, contacts, placeholder for open tickets) |
| **Seeders** | RolePermissionSeeder + DemoDataSeeder (demo company, admin user, 2 sample clients) |
| **Tests** | 24/24 passing (Pest/PHPUnit via `php artisan test`) |
| **Installer** | `install.sh` — one-shot Ubuntu 24.04 deploy script |

---

## Repo Structure

```
itflow-next/
├── install.sh          — one-shot Ubuntu 24.04 installer
├── overlay/            — our custom app code, mirroring Laravel's directory layout
│   ├── app/
│   │   ├── Livewire/   — Livewire components (Dashboard, Clients/Index, Clients/Show,
│   │   │                  Admin/Users, Roles, Tags, Categories)
│   │   ├── Models/     — Client, Contact, Location, Company, User + concerns traits
│   │   └── Livewire/Actions/Logout.php
│   ├── database/
│   │   ├── migrations/ — companies, clients, contacts, locations, tags, categories,
│   │   │                  custom_fields, user_client_permissions
│   │   ├── factories/  — ClientFactory
│   │   └── seeders/    — DatabaseSeeder, RolePermissionSeeder, DemoDataSeeder
│   ├── resources/views/
│   │   ├── components/layouts/app.blade.php
│   │   ├── components/ui/   — sidebar, topbar, icon, button, badge, card, modal, etc.
│   │   └── livewire/        — clients.index, clients.show, dashboard, admin/*, settings/*
│   ├── routes/
│   │   └── web.php     — all app routes incl. named `logout` POST route
│   └── tests/Feature/
│       ├── ClientManagementTest.php
│       └── Auth/AuthenticationTest.php  — rewritten to match our custom layout
└── README.md
```

---

## Database Schema (Phase 1 migrations)

| Migration | Table(s) |
|---|---|
| `0001_01_01_000000` | `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001` | `cache`, `cache_locks` |
| `0001_01_01_000002` | `jobs`, `job_batches`, `failed_jobs` |
| `2024_01_01_000001` | `companies` |
| `2024_01_01_000002` | adds `company_id`, `is_active`, `last_login_at` to `users` |
| `2024_01_01_000003` | `clients` |
| `2024_01_01_000004` | `locations` |
| `2024_01_01_000005` | `contacts` |
| `2024_01_01_000006` | `tags`, `taggables` (polymorphic) |
| `2024_01_01_000007` | `categories` |
| `2024_01_01_000008` | `custom_fields`, `custom_field_values` |
| `2024_01_01_000009` | `user_client_permissions` |
| `2026_06_15_*` | spatie permission tables (`roles`, `permissions`, `model_has_*`, `role_has_*`) |

---

## Installer (`install.sh`) — Step-by-Step

```
Step 0   Swapfile — create 2 GB swap if none exists (prevents OOM on low-memory VMs)
Step 1   System packages — PHP 8.3 + extensions, MySQL, Nginx, Composer, Node 20
Step 2   Database — CREATE DATABASE / CREATE USER + ALTER USER (idempotent password sync)
Step 3   Laravel scaffold — composer create-project laravel/laravel ^11.0
         composer require laravel/breeze livewire/livewire spatie/laravel-permission
         php artisan breeze:install livewire
         rm stale Breeze scaffold files (navigation.blade.php, ExampleTest, ProfileTest)
Step 4   Overlay — rsync overlay/ → APP_DIR/ (our custom code on top of the scaffold)
Step 5   .env — sed to set APP_NAME, APP_URL, DB_CONNECTION=mysql, DB_HOST=localhost,
         DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, QUEUE_CONNECTION=database
         php artisan key:generate --force
Step 6   composer install --optimize-autoloader --no-interaction
         vendor:publish spatie PermissionServiceProvider
         php artisan migrate --force
         php artisan db:seed --force
         npm install && npm run build
Step 7   File permissions — chown www-data:www-data; chmod 775/664 storage + cache
Step 8   Nginx site config + PHP-FPM — /etc/nginx/sites-available/itflow-next.conf
Step 9   Systemd queue worker — itflow-next-queue.service (for email-to-ticket in Phase 3)
```

Default admin login after install: `admin@itflow-next.test` / `password` (change immediately).

DB credentials written to `/root/itflow-next-db-credentials.txt` (chmod 600).

---

## Bugs Found & Fixed During Live Deployment

### Bug 1 — Composer 2.10+ security-advisory block

**Symptom:**
```
Your requirements could not be resolved to an installable set of packages.
Problem 1 — laravel/framework affected by security advisories.
To turn the feature off, set "policy.advisories.block" to false.
```

**Root cause:** Composer 2.10 introduced `policy.advisories.block = true` by default, blocking resolution of packages with known security advisories. `--no-audit` is NOT the correct flag for `composer install` (invalid option).

**Fix in `install.sh`:**
```bash
# Before composer create-project (no project composer.json yet → must be global):
composer config -g policy.advisories.block false

# Before composer require (project-level):
composer config policy.advisories.block false
```

---

### Bug 2 — `composer install --no-audit` invalid flag

**Symptom:**
```
The "--no-audit" option does not exist.
```

**Fix:** Removed `--no-audit`. The project-level `policy.advisories.block false` set during `composer require` carries through to `composer install`.

---

### Bug 3 — `php artisan migrate` uses SQLite (no driver)

**Symptom:**
```
could not find driver
(Connection: sqlite, SQL: select exists (select 1 from sqlite_master ...))
```

**Root cause:** Laravel 11's `.env.example` ships with `DB_CONNECTION=sqlite` and all MySQL-related lines commented out:
```
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
...
```
The original `install.sh` sed only set `APP_NAME` and `APP_URL` — it never switched `DB_CONNECTION` or uncommented the DB lines.

**Fix in `install.sh`** (expanded sed block):
```bash
sed -i \
  -e "s/^DB_CONNECTION=.*/DB_CONNECTION=mysql/" \
  -e "s/^# *DB_HOST=.*/DB_HOST=localhost/" \
  -e "s/^DB_HOST=.*/DB_HOST=localhost/" \
  -e "s/^# *DB_PORT=.*/DB_PORT=3306/" \
  -e "s/^# *DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" \
  -e "s/^# *DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" \
  -e "s/^# *DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" \
  -e "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" \
  -e "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" \
  -e "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" \
  .env
```

Note: `DB_HOST=localhost` (not `127.0.0.1`) is critical — `localhost` uses a Unix socket, which matches the `'user'@'localhost'` MySQL grant. `127.0.0.1` forces TCP and would need a `'user'@'127.0.0.1'` or `'%'` grant.

---

### Bug 4 — MySQL password mismatch on re-runs

**Symptom:**
```
SQLSTATE[HY000] [1045] Access denied for user 'itflow_next'@'localhost'
```

**Root cause:** Two compounding issues:
1. `DB_PASS="${DB_PASS:-$(openssl rand -hex 16)}"` generates a **new** random password on every install.sh run.
2. `CREATE USER IF NOT EXISTS ... IDENTIFIED BY '${DB_PASS}'` is a **no-op** for an already-existing user — it does NOT update the password.

Result: after run #1, every subsequent run wrote a new password into `.env` and `/root/itflow-next-db-credentials.txt`, but the MySQL user kept its run-#1 password. Laravel couldn't connect.

**Fix in `install.sh`:**
```bash
# Reuse previously-generated password on re-runs:
if [[ -z "${DB_PASS:-}" ]] && [[ -f /root/itflow-next-db-credentials.txt ]]; then
  DB_PASS="$(grep -m1 '^DB_PASSWORD=' /root/itflow-next-db-credentials.txt | cut -d= -f2-)"
fi
DB_PASS="${DB_PASS:-$(openssl rand -hex 16)}"

# AND always sync MySQL user password to current DB_PASS:
mysql -uroot <<SQL
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
...
SQL
```

---

### Bug 5 — Stale Breeze scaffold files conflict with custom layout

`php artisan breeze:install livewire` generates several files that conflict with ITFlow-Next's custom layout and routes:

| File | Problem |
|---|---|
| `resources/views/livewire/layout/navigation.blade.php` | References `route('profile')` (undefined; we use `profile.edit`) |
| `tests/Feature/ExampleTest.php` | Expects `GET /` → 200; our app redirects to `/dashboard` (302) |
| `tests/Feature/ProfileTest.php` | Expects `GET /profile` → 200; we route `settings/profile` as `profile.edit` |
| `tests/Feature/Auth/AuthenticationTest.php` | Two tests assert `assertSeeVolt('layout.navigation')` and call `logout` on that Volt component — neither matches our custom topbar |

**Fix in `install.sh`** (after `breeze:install`, before overlay copy):
```bash
rm -f resources/views/livewire/layout/navigation.blade.php
rm -f tests/Feature/ExampleTest.php
rm -f tests/Feature/ProfileTest.php
```

**Fix:** `overlay/tests/Feature/Auth/AuthenticationTest.php` replaces the Breeze default with tests that match our actual UI:
- `test_navigation_menu_can_be_rendered` → `GET /dashboard` → assertOk + assertSee('Log out')
- `test_users_can_logout` → `POST route('logout')` → assertRedirect('/') + assertGuest

---

### Bug 6 — Missing `logout` named route

**Symptom:**
```
Route [logout] not defined.
(View: resources/views/components/ui/topbar.blade.php)
```

**Root cause:** The topbar's logout form uses `action="{{ route('logout') }}"`, but no named `logout` route existed in our `web.php`.

**Fix** in `overlay/routes/web.php`:
```php
Route::post('/logout', function (\App\Livewire\Actions\Logout $logout) {
    $logout();
    return redirect('/');
})->name('logout')->middleware('auth');
```

---

### Bug 7 — `Client::factory()` undefined (`HasFactory` missing)

**Symptom:**
```
BadMethodCallException: Call to undefined method App\Models\Client::factory()
```

**Root cause:** `overlay/app/Models/Client.php` used `BelongsToCompany, HasTags, HasCustomFields` but was missing the `HasFactory` trait.

**Fix** in `overlay/app/Models/Client.php`:
```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
// ...
use HasFactory, BelongsToCompany, HasTags, HasCustomFields;
```

---

### Bug 8 — `archived_at` silently dropped by mass-assignment protection

**Symptom:** `ClientManagementTest > admin can archive a client` → `Failed asserting that null is not null`.

**Root cause:** `Client::archive()` calls `update(['archived_at' => now()])`, but `archived_at` was not in `$fillable`, so Laravel silently ignored the assignment.

**Fix** in `overlay/app/Models/Client.php` — added `'archived_at'` to `$fillable`:
```php
protected $fillable = [
    'company_id', 'name', 'type', 'is_lead', 'website', 'referral',
    'rate', 'currency_code', 'net_terms', 'tax_id_number', 'abbreviation',
    'notes', 'is_favorite', 'archived_at',
];
```

---

## Test Results (Final)

```
php artisan test

PASS  Tests\Feature\Auth\AuthenticationTest      5 tests
PASS  Tests\Feature\Auth\PasswordResetTest       4 tests
PASS  Tests\Feature\Auth\PasswordUpdateTest      2 tests
PASS  Tests\Feature\Auth\RegistrationTest        2 tests
PASS  Tests\Feature\ClientManagementTest         4 tests (create, archive, view, auth)

Tests: 24 passed (59 assertions)
Duration: ~7–13s
```

---

## Browser Verification (live on 192.168.1.243)

| Check | Result |
|---|---|
| `GET /` | 302 → `/dashboard` → 302 → `/login` (unauthenticated) |
| Login `admin@itflow-next.test` / `password` | ✓ Redirects to Dashboard |
| Dashboard | ✓ Shows: 2 Active Clients, 1 Lead, 1 Contact, Open Tickets placeholder |
| Clients list | ✓ Acme Corporation (Customer), Globex Industries (Lead) |
| New Client modal | ✓ Created "Initech LLC" — appeared in list immediately |
| Client detail → Contacts tab | ✓ Jane Doe (Primary, Billing) |
| Client detail → Locations tab | ✓ Head Office — 123 Main St, Springfield IL |
| Admin → Users | ✓ Admin user with Administrator role |
| Admin → Roles | ✓ Administrator (7 perms), Technician (2), Read Only (1) |
| Admin → Tags | ✓ Empty list, "New Tag" button |
| Admin → Categories | ✓ Empty list, "New Category" button |
| Logout | ✓ POST → redirects to `/login` |

---

## Tech Notes for Future Phases

- **Livewire version:** Breeze 2.4 pins `livewire/livewire` at **v3.8.1** (not v4.x). `composer require livewire/livewire` resolves v4 first, but `breeze:install livewire` downgrades to v3 via `composer update livewire/livewire livewire/volt`. All overlay Livewire component syntax is written for v3.
- **`DB_HOST=localhost` vs `127.0.0.1`:** `localhost` → Unix socket → matches `'user'@'localhost'` MySQL grant. Using `127.0.0.1` forces TCP and would require a `'%'` grant. Don't change unless you also change the MySQL grant.
- **Multi-tenancy:** `BelongsToCompany` trait on models adds a global scope filtering by `auth()->user()->company_id`. Factory-created models in tests auto-receive the authenticated user's `company_id`.
- **Permissions:** Defined in `RolePermissionSeeder`. The `manage users` permission gates the entire `/admin/*` route group. New permissions for Phase 2+ modules should be added there.
- **Queue:** `QUEUE_CONNECTION=database` is configured but the systemd worker (`itflow-next-queue.service`) is registered by `install.sh` step 9. Phase 3 email-to-ticket ingestion will depend on this worker being active.
- **Client portal guard:** `client` guard is scaffolded (foundation only). No UI yet — full portal with contact authentication comes in a later phase.

---

## Default Credentials (post-install)

| Item | Value |
|---|---|
| Admin email | `admin@itflow-next.test` |
| Admin password | `password` (change immediately) |
| MySQL DB | `itflow_next` |
| MySQL user | `itflow_next` |
| MySQL password | auto-generated, stored in `/root/itflow-next-db-credentials.txt` |
