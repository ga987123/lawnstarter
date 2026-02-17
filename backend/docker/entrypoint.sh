#!/bin/sh
set -e

# Ensure Laravel storage framework dirs exist and are writable (e.g. for Blade view cache)
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Declare RabbitMQ queue so it exists before any app code connects
if [ -n "${RABBITMQ_HOST}" ]; then
  echo "Declaring RabbitMQ queue 'default'..."
  php artisan rabbitmq:queue-declare default rabbitmq
fi

exec "$@"
