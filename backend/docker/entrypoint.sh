#!/bin/sh
set -e

# Declare RabbitMQ queue so it exists before any app code connects
if [ -n "${RABBITMQ_HOST}" ]; then
  echo "Declaring RabbitMQ queue 'default'..."
  php artisan rabbitmq:queue-declare default rabbitmq
fi

exec "$@"
