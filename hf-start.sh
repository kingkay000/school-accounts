#!/usr/bin/env bash
set -euo pipefail

cd /app

# Create .env when not provided by the runtime.
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Ensure SQLite database file exists for default config.
mkdir -p database
if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
fi

# Ensure APP_KEY exists (required for sessions/cookies/auth).
if ! grep -qE '^APP_KEY=base64:' .env && [ -z "${APP_KEY:-}" ]; then
  php artisan key:generate --force
fi

# Ensure schema exists before login/auth queries run.
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=7860
