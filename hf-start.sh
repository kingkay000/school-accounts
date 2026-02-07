#!/usr/bin/env bash
set -euo pipefail

cd /app

set_env_value() {
  local key="$1"
  local value="$2"

  if grep -qE "^${key}=" .env; then
    sed -i "s#^${key}=.*#${key}=${value}#" .env
  else
    printf "\n%s=%s\n" "$key" "$value" >> .env
  fi
}

# Create .env when not provided by the runtime.
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Default to MySQL while using file-backed session/cache when runtime env vars are not provided.
# HF Secrets can still override these values.
if [ -z "${DB_CONNECTION:-}" ]; then
  set_env_value "DB_CONNECTION" "mysql"
fi

if [ -z "${SESSION_DRIVER:-}" ]; then
  set_env_value "SESSION_DRIVER" "file"
fi

if [ -z "${CACHE_STORE:-}" ]; then
  set_env_value "CACHE_STORE" "file"
fi

# Ensure APP_KEY exists (required for sessions/cookies/auth).
if ! grep -qE '^APP_KEY=base64:' .env && [ -z "${APP_KEY:-}" ]; then
  php artisan key:generate --force
fi

# Ensure schema exists before login/auth queries run.
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=7860
