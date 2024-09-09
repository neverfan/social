setup:
	make up
	make install
	sleep 10 && make migrate

install:
	docker compose run --rm php-fpm composer install --ignore-platform-reqs

update:
	docker compose run --rm php-fpm composer update --ignore-platform-reqs

up:
	docker compose up -d

migrate:
	docker compose run --rm php-fpm php artisan migrate:fresh

migrate-testing:
	docker compose run -e DB_CONNECTION=testing --rm php-fpm php artisan migrate:fresh

tinker:
	docker compose run --rm php-fpm php artisan tinker
