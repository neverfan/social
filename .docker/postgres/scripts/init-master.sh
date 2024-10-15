#!/bin/bash
set -e

if [ "$POSTGRES_REPLICA_MODE" != 'master' ]; then
    exit 0
fi

echo "===================================================="
echo "INIT MASTER on hostname: $(hostname)"
echo "===================================================="

/usr/local/bin/docker-ensure-initdb.sh

#путь к основному файлу конфигурации
POSTGRES_CONFIG_PATH="$PGDATA/postgresql.conf"

# Прописываем отдельный путь расширения конфигурации
grep -qxF "include_if_exists = '/etc/postgresql/postgresql.extend.conf'"  "$POSTGRES_CONFIG_PATH" || echo "include_if_exists = '/etc/postgresql/postgresql.extend.conf'" >> "$POSTGRES_CONFIG_PATH"

#Создаем пользователя для репликации
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
DO \$$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_catalog.pg_roles WHERE rolname = 'replicator') THEN
    CREATE ROLE replicator WITH REPLICATION LOGIN PASSWORD '$POSTGRES_REPLICA_PASSWORD';
  END IF;
END \$$;
EOSQL

#запускаем ванильный entrypoint
exec /usr/local/bin/docker-entrypoint.sh "$@"

