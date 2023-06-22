install:
	make down
	make build

build:
	docker-compose up -d --build

up:
	docker-compose up -d --remove-orphans

down:
	docker-compose down --remove-orphans

re:
	make down
	make up

bash:
	docker-compose exec php bash

composer-update:
	docker-compose exec php composer update -W

migration:
	docker-compose exec php bin/console doctrine:migrations:diff --allow-empty-diff

migrate:
	docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

entity:
	docker-compose exec php bin/console make:entity

cc:
	docker-compose exec php bin/console cache:clear

cs-fix:
	docker-compose exec php vendor/bin/php-cs-fixer fix --allow-risky=yes