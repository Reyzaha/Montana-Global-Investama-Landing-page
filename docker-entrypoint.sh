#!/bin/bash
set -e

# PT MONTANA GLOBAL INVESTAMA — DOCKER ENTRYPOINT
# Automatically ensure upload directories and runtime folders are writable by Apache www-data

mkdir -p /var/www/html/assets/uploads/projects
mkdir -p /var/www/html/assets/uploads/articles
mkdir -p /var/www/html/backend/cache

# Set permissions for upload directory
chown -R www-data:www-data /var/www/html/assets/uploads /var/www/html/backend/cache 2>/dev/null || true
chmod -R 775 /var/www/html/assets/uploads /var/www/html/backend/cache 2>/dev/null || true

exec "$@"
