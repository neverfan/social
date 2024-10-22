include .env

setup:
	make up
	make install
	sleep 10 && make migrate

install:
	docker compose run --rm php-fpm composer install --ignore-platform-reqs
	docker compose run --rm php-fpm artisan horizon:install

update:
	docker compose run --rm php-fpm composer update --ignore-platform-reqs

up:
	docker compose up -d

#############################################
#			RUN DB MIGRATIONS				#
#############################################

migrate:
	docker compose run --rm php-fpm php artisan migrate

migrate-testing:
	docker compose run -e DB_CONNECTION=testing --rm php-fpm php artisan migrate:fresh

tinker:
	docker compose run --rm php-fpm php -d memory_limit=2G artisan tinker

#############################################
#			UPLOAD USERS DATA				#
#############################################

load-users:
	docker compose run --rm php-fpm php -d memory_limit=2G artisan app:load-user-data

#############################################
#			REPLICATAION SWITCH				#
#############################################

# Перезагрузка конфигурации
pg-reload-conf:
	docker compose exec ${MASTER_CONTAINER_NAME} psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT pg_reload_conf();"

# Вывести информацию о текущих настройках репликации
sync-state:
	docker compose exec ${MASTER_CONTAINER_NAME} psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT application_name, client_addr, state, sync_state FROM pg_stat_replication;"

# Синхронная репликация
sync-replication:
	docker compose exec ${MASTER_CONTAINER_NAME} psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET synchronous_standby_names = 'FIRST 2 (${SLAVE_NAME_1}, ${SLAVE_NAME_2})';"
	make pg-reload-conf
	make sync-state

# Асинхронная репликация
async-replication:
	docker compose exec ${MASTER_CONTAINER_NAME} psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET synchronous_standby_names = '';"
	make pg-reload-conf
	make sync-state

# Полу-синхронная репликация
half-sync-replication:
	docker compose exec ${MASTER_CONTAINER_NAME} psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET synchronous_standby_names = 'FIRST 1 (${SLAVE_NAME_1}, ${SLAVE_NAME_2})';"
	make pg-reload-conf
	make sync-state

# Кворумная репликация
quorum-replication:
	docker compose exec ${MASTER_CONTAINER_NAME} psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET synchronous_standby_names = 'ANY 1 (${SLAVE_NAME_1}, ${SLAVE_NAME_2})';"
	make pg-reload-conf
	make sync-state

#############################################
#			DB2 PROMOTE TO MASTER			#
#############################################

# Промоут db2 -> master
promote-db2:
	docker compose up -d

	# Изменения для db2 (new master)
	docker compose exec db2 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET primary_conninfo = '';"
	docker compose exec db2 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET synchronous_standby_names = 'ANY 1 (db1, db3)';"
	docker compose exec db2 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT pg_reload_conf();"
	docker compose exec db2 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "select pg_promote();"
	docker compose exec db2 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT * FROM pg_create_physical_replication_slot('db1');"
	docker compose exec db2 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT * FROM pg_create_physical_replication_slot('db3');"
	docker compose exec db2 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT pg_reload_conf();"

	# Изменения для db1 (new replica)
	docker compose exec db1 touch "${PGDATA}/standby.signal"
	docker compose exec db1 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET synchronous_standby_names = '';"
	docker compose exec db1 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET primary_conninfo = 'user=replicator password=${DB_REPLICA_PASSWORD} channel_binding=prefer host=${SLAVE_NAME_1} port=5432 sslmode=prefer sslcompression=0 sslcertmode=allow sslsni=1 ssl_min_protocol_version=TLSv1.2 gssencmode=prefer krbsrvname=postgres gssdelegation=0 target_session_attrs=any load_balance_hosts=disable application_name=${MASTER_CONTAINER_NAME}';"
	docker compose exec db1 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET primary_slot_name = 'db1';"
	docker compose exec db1 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT pg_reload_conf();"

	# Изменения для db3 (replica)
	docker compose exec db3 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "ALTER SYSTEM SET primary_conninfo = 'user=replicator password=${DB_REPLICA_PASSWORD} channel_binding=prefer host=${SLAVE_NAME_1} port=5432 sslmode=prefer sslcompression=0 sslcertmode=allow sslsni=1 ssl_min_protocol_version=TLSv1.2 gssencmode=prefer krbsrvname=postgres gssdelegation=0 target_session_attrs=any load_balance_hosts=disable application_name=${SLAVE_NAME_2}';"
	docker compose exec db3 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT pg_reload_conf();"

	# Проверяем результат измненений
	docker compose exec db2 psql -U ${DB_USERNAME} -d ${DB_DATABASE} -c "SELECT application_name, client_addr, state, sync_state FROM pg_stat_replication;"

# Сброс хранилищ с БД
drop-dbs:
	docker compose down
	docker volume rm social_volume-db1 social_volume-db2 social_volume-db3
	docker compose up -d
