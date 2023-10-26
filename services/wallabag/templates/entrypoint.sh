#!/bin/sh
INSTALLED="$(psql -qAt -h "${SYMFONY__ENV__DATABASE_HOST}" -p "${SYMFONY__ENV__DATABASE_PORT}" -U "${POSTGRES_USER}" \
  -d "${SYMFONY__ENV__DATABASE_NAME}" \
  -c "SELECT 1 FROM pg_catalog.pg_tables WHERE tablename = 'migration_versions';")"

if [ "$INSTALLED" != "1" ]; then
  php bin/console wallabag:install --env=prod -n
fi

rm -rf /var/www/wallabag/var/cache

nginx -c /etc/nginx/nginx.conf -e /tmp/error.log
php-fpm --nodaemonize
