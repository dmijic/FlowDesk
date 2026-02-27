.PHONY: setup up down reset test logs shell

setup:
	./scripts/setup.sh

up:
	docker compose up -d

down:
	docker compose down

reset:
	./scripts/reset.sh

test:
	./scripts/test.sh

logs:
	docker compose logs -f --tail=200

shell:
	docker compose exec php sh
