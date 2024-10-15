#!/bin/bash
set -e

if [ "$POSTGRES_REPLICA_MODE" != 'slave' ]; then
    exit 0
fi

echo "===================================================="
echo "INIT SLAVE on hostname: $(hostname)"
echo "===================================================="

rm -rf $PGDATA/*

#экспорт пароля из ENV для pg_basebackup
export PGPASSWORD="$POSTGRES_REPLICA_PASSWORD"

#копируем backup
pg_basebackup -h "$POSTGRES_MASTER_HOST" -D "$PGDATA" -U replicator -Fp -Xs -P -R -C -S "$(hostname)" -v

#Создаем standby.signal
touch "$PGDATA/standby.signal"

#Прописываем подключение к мастеру
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
ALTER SYSTEM SET primary_conninfo = 'user=replicator password=$POSTGRES_REPLICA_PASSWORD channel_binding=prefer host=$POSTGRES_MASTER_HOST port=5432 sslmode=prefer sslcompression=0 sslcertmode=allow sslsni=1 ssl_min_protocol_version=TLSv1.2 gssencmode=prefer krbsrvname=postgres gssdelegation=0 target_session_attrs=any load_balance_hosts=disable application_name=$(hostname)';
SELECT pg_reload_conf();
EOSQL

