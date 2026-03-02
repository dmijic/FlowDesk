.PHONY: setup dev-up dev-down reset test build lint logs shell prod-build prod-up prod-down prod-logs

setup:
	./scripts/setup.sh

dev-up:
	docker compose up -d --build

dev-down:
	docker compose down

reset:
	./scripts/reset.sh

test:
	./scripts/test.sh

build:
	./scripts/build.sh

lint:
	./scripts/lint.sh

logs:
	docker compose logs -f --tail=200

shell:
	docker compose exec php sh

prod-build:
	docker compose -f docker-compose.prod.yml build

prod-up:
	./scripts/deploy-prod.sh

prod-down:
	docker compose -f docker-compose.prod.yml down

prod-logs:
	docker compose -f docker-compose.prod.yml logs -f --tail=200
