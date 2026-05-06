#!/bin/sh

# WHY:
# Volume mounts can override file permissions,
# so we ensure executable flag at runtime.
chmod +x /var/www/docker/entrypoint.sh 2>/dev/null || true

echo "Starting backend container..."

# Install composer deps if not installed
if [ ! -d "vendor" ]; then
  echo "Installing composer dependencies..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# WHY:
# On fresh container start, .env may not exist.
# We auto-create it from .env.example for easier setup.
if [ ! -f ".env" ]; then
  echo "⚙️ .env not found. Creating from .env.example..."
  cp .env.example .env
fi

# Generate app key if not exists
if ! grep -q "APP_KEY=base64" .env; then
  echo "🔑 Generating APP_KEY..."
  php artisan key:generate
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed if empty
echo "Seeding database..."
php artisan db:seed --force

# Clear caches
php artisan config:clear
php artisan cache:clear

echo "Backend ready"

if [ "$#" -eq 0 ]; then
  set -- sh -c "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache && php-fpm"
fi

exec "$@"