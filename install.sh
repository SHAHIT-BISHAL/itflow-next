#!/usr/bin/env bash
#
# ITFlow-Next — one-shot installer for Ubuntu 24.04
#
# This script:
#   1. Installs PHP 8.3, MySQL, Nginx, Composer, Node.js 20
#   2. Scaffolds a fresh Laravel 11 app
#   3. Installs Livewire 3, Breeze (Livewire stack), Tailwind, spatie/laravel-permission
#   4. Copies the ITFlow-Next overlay (custom app code from this repo) into the project
#   5. Configures .env, generates app key, creates the database
#   6. Runs migrations + seeders
#   7. Builds front-end assets
#   8. Configures Nginx + PHP-FPM + a systemd queue worker service
#
# Usage:
#   sudo bash install.sh
#
# Re-running is safe for steps 1-3 (idempotent installs); steps 4+ will
# overwrite overlay files and re-run migrations.

set -euo pipefail

# ----------------------------------------------------------------------------
# Configuration — edit these or pass as environment variables before running
# ----------------------------------------------------------------------------
APP_NAME="${APP_NAME:-ITFlow-Next}"
APP_DIR="${APP_DIR:-/var/www/itflow-next}"
APP_DOMAIN="${APP_DOMAIN:-itflow-next.local}"
DB_NAME="${DB_NAME:-itflow_next}"
DB_USER="${DB_USER:-itflow_next}"
# Reuse a previously-generated password on re-runs so it stays in sync with the
# MySQL user (which a re-run does NOT update, since CREATE USER IF NOT EXISTS is
# a no-op for an existing user) and with the existing app's .env.
if [[ -z "${DB_PASS:-}" ]] && [[ -f /root/itflow-next-db-credentials.txt ]]; then
  DB_PASS="$(grep -m1 '^DB_PASSWORD=' /root/itflow-next-db-credentials.txt | cut -d= -f2-)"
fi
DB_PASS="${DB_PASS:-$(openssl rand -hex 16)}"
PHP_VERSION="${PHP_VERSION:-8.3}"
OVERLAY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/overlay"

echo "=== ITFlow-Next installer ==="
echo "App dir:    $APP_DIR"
echo "Domain:     $APP_DOMAIN"
echo "DB name:    $DB_NAME"
echo "DB user:    $DB_USER"
echo "Overlay:    $OVERLAY_DIR"
echo

if [[ "$EUID" -ne 0 ]]; then
  echo "Please run as root (sudo bash install.sh)" >&2
  exit 1
fi

# ----------------------------------------------------------------------------
# 1. System packages: PHP, MySQL, Nginx, Composer, Node
# ----------------------------------------------------------------------------
echo "--- Installing system packages ---"
apt-get update -y
apt-get install -y software-properties-common ca-certificates apt-transport-https lsb-release gnupg curl unzip git rsync

# ----------------------------------------------------------------------------
# 0. Ensure swap exists (Composer/npm builds can OOM on low-memory VMs)
# ----------------------------------------------------------------------------
if [[ "$(swapon --show | wc -l)" -eq 0 ]] && [[ ! -f /swapfile ]]; then
  echo "--- No swap detected, creating a 2G swapfile ---"
  fallocate -l 2G /swapfile
  chmod 600 /swapfile
  mkswap /swapfile
  swapon /swapfile
  echo "/swapfile none swap sw 0 0" >> /etc/fstab
fi

# PHP (ondrej/php PPA provides current PHP versions on Ubuntu)
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y \
  "php${PHP_VERSION}" "php${PHP_VERSION}-fpm" "php${PHP_VERSION}-cli" \
  "php${PHP_VERSION}-mysql" "php${PHP_VERSION}-mbstring" "php${PHP_VERSION}-xml" \
  "php${PHP_VERSION}-curl" "php${PHP_VERSION}-zip" "php${PHP_VERSION}-bcmath" \
  "php${PHP_VERSION}-gd" "php${PHP_VERSION}-intl" "php${PHP_VERSION}-imap" \
  "php${PHP_VERSION}-redis"

# Composer
if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Node.js 20 (NodeSource)
if ! command -v node >/dev/null 2>&1; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
fi

# MySQL
apt-get install -y mysql-server

# Nginx
apt-get install -y nginx

echo "--- System packages installed ---"

# ----------------------------------------------------------------------------
# 2. Database setup
# ----------------------------------------------------------------------------
echo "--- Configuring MySQL database ---"
mysql -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "DB credentials saved to /root/itflow-next-db-credentials.txt"
cat > /root/itflow-next-db-credentials.txt <<EOF
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}
EOF
chmod 600 /root/itflow-next-db-credentials.txt

# ----------------------------------------------------------------------------
# 3. Scaffold Laravel app (skip if already present)
# ----------------------------------------------------------------------------
if [[ ! -f "$APP_DIR/artisan" ]]; then
  echo "--- Creating Laravel project at $APP_DIR ---"
  mkdir -p "$(dirname "$APP_DIR")"
  # Composer 2.10+ blocks installs of packages affected by security advisories by
  # default; disable globally so create-project's internal install can resolve.
  composer config -g policy.advisories.block false
  composer create-project laravel/laravel "$APP_DIR" "^11.0"
else
  echo "--- Laravel project already exists at $APP_DIR, skipping create-project ---"
fi

cd "$APP_DIR"

echo "--- Installing Composer packages (Breeze, Livewire, permissions) ---"
# Composer 2.10+ blocks installs of packages affected by security advisories by
# default. Disable that block so create-project/require can resolve.
composer config policy.advisories.block false
composer require laravel/breeze livewire/livewire spatie/laravel-permission --no-interaction

echo "--- Installing Breeze (Livewire stack) ---"
# --no-interaction with breeze:install livewire installs the Livewire/Volt + Tailwind starter kit
php artisan breeze:install livewire --no-interaction || true

# Remove Breeze scaffold files that don't match ITFlow-Next's custom layout/routes
# (our overlay provides its own auth tests; the rest are unused by our UI).
rm -f resources/views/livewire/layout/navigation.blade.php
rm -f tests/Feature/ExampleTest.php
rm -f tests/Feature/ProfileTest.php

# ----------------------------------------------------------------------------
# 4. Copy ITFlow-Next overlay (custom code from this repo) into the project
# ----------------------------------------------------------------------------
echo "--- Applying ITFlow-Next overlay ---"
if [[ -d "$OVERLAY_DIR" ]]; then
  rsync -a "$OVERLAY_DIR"/ "$APP_DIR"/
else
  echo "WARNING: overlay directory not found at $OVERLAY_DIR — skipping custom app code copy"
fi

# ----------------------------------------------------------------------------
# 5. Configure .env
# ----------------------------------------------------------------------------
echo "--- Configuring .env ---"
if [[ ! -f .env ]]; then
  cp .env.example .env
fi

sed -i \
  -e "s/^APP_NAME=.*/APP_NAME=\"${APP_NAME}\"/" \
  -e "s/^APP_URL=.*/APP_URL=http:\/\/${APP_DOMAIN}/" \
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

grep -q "^QUEUE_CONNECTION=" .env && sed -i "s/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/" .env || echo "QUEUE_CONNECTION=database" >> .env

php artisan key:generate --force

# ----------------------------------------------------------------------------
# 6. Composer / npm install, permissions config publish, migrate + seed
# ----------------------------------------------------------------------------
echo "--- composer install ---"
# Dev dependencies (Pest/PHPUnit) are kept so `php artisan test` works on this box.
# For a hardened production deploy, re-run with `composer install --no-dev` afterwards.
composer install --optimize-autoloader --no-interaction

echo "--- Publishing spatie/laravel-permission config ---"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction || true

echo "--- Running migrations + seeders ---"
php artisan migrate --force
php artisan db:seed --force

echo "--- Installing & building front-end assets ---"
npm install
npm run build

# ----------------------------------------------------------------------------
# 7. File permissions
# ----------------------------------------------------------------------------
echo "--- Setting permissions ---"
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} \;

# ----------------------------------------------------------------------------
# 8. Nginx + PHP-FPM site config
# ----------------------------------------------------------------------------
echo "--- Configuring Nginx ---"
cat > /etc/nginx/sites-available/itflow-next.conf <<NGINX
server {
    listen 80;
    server_name ${APP_DOMAIN};
    root ${APP_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/itflow-next.conf /etc/nginx/sites-enabled/itflow-next.conf
[[ -f /etc/nginx/sites-enabled/default ]] && rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl enable --now "php${PHP_VERSION}-fpm"

# ----------------------------------------------------------------------------
# 9. Queue worker (for future email-to-ticket processing, notifications, etc.)
# ----------------------------------------------------------------------------
echo "--- Configuring queue worker systemd service ---"
cat > /etc/systemd/system/itflow-next-queue.service <<SERVICE
[Unit]
Description=ITFlow-Next Queue Worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=${APP_DIR}

[Install]
WantedBy=multi-user.target
SERVICE

systemctl daemon-reload
systemctl enable --now itflow-next-queue

echo
echo "=== Install complete ==="
echo "Visit: http://${APP_DOMAIN}"
echo "Default admin login: admin@itflow-next.test / password (CHANGE THIS IMMEDIATELY)"
echo "DB credentials: /root/itflow-next-db-credentials.txt"
