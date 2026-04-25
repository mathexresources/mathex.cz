.PHONY: up down restart shell shell-mysql logs build install migrate rollback seed db-reset \
        phinx-status phinx-create phinx-migrate phinx-rollback phinx-seed \
        console migrate-cmd seed-cmd create-admin clear-cache generate-sitemap export-meetings \
        stan test cleanup help

PHINX = docker-compose exec app vendor/bin/phinx

# ─── Docker ────────────────────────────────────────────────────────────────────
up:
	@cp -n .env.example .env 2>/dev/null || true
	docker-compose up -d --build
	@echo ""
	@echo "  ✔  App:      http://localhost"
	@echo "  ✔  Adminer:  http://localhost:8080  (server=mysql, user=mathex)"
	@echo "  ✔  Mailpit:  http://localhost:8025"
	@echo ""

down:
	docker-compose down

restart:
	docker-compose restart

build:
	docker-compose build --no-cache

# ─── Shells ────────────────────────────────────────────────────────────────────
shell:
	docker-compose exec app sh

shell-mysql:
	docker-compose exec mysql mysql -u $${DB_USER:-mathex} -p$${DB_PASSWORD:-mathex_secret} $${DB_NAME:-mathex}

# ─── Logs ─────────────────────────────────────────────────────────────────────
logs:
	docker-compose logs -f

logs-app:
	docker-compose logs -f app

# ─── Composer ─────────────────────────────────────────────────────────────────
install:
	docker-compose exec app composer install

update:
	docker-compose exec app composer update

# ─── Phinx migrations (primary) ───────────────────────────────────────────────
migrate: phinx-migrate

phinx-migrate:
	$(PHINX) migrate -e development

phinx-rollback:
	$(PHINX) rollback -e development

phinx-status:
	$(PHINX) status -e development

phinx-create:
	@test -n "$(name)" || (echo "Usage: make phinx-create name=CreateFooTable" && exit 1)
	$(PHINX) create $(name)

# ─── Seeds ────────────────────────────────────────────────────────────────────
seed: phinx-seed

phinx-seed:
	$(PHINX) seed:run -s MainSeeder -e development

phinx-seed-one:
	@test -n "$(name)" || (echo "Usage: make phinx-seed-one name=UserSeeder" && exit 1)
	$(PHINX) seed:run -s $(name) -e development

# ─── Database shortcuts ────────────────────────────────────────────────────────
db-reset:
	$(PHINX) migrate -e development
	$(PHINX) seed:run -s MainSeeder -e development

db-fresh:
	$(PHINX) rollback -e development -t 0
	$(PHINX) migrate -e development
	$(PHINX) seed:run -s MainSeeder -e development

# ─── Development ──────────────────────────────────────────────────────────────
clear-cache:
	docker-compose exec app rm -rf temp/cache temp/proxies
	@echo "Cache cleared."

stan:
	docker-compose exec app vendor/bin/phpstan analyse

test:
	docker-compose exec app vendor/bin/phpunit

# ─── CLI (bin/console) ────────────────────────────────────────────────────────
console:
	docker-compose exec app php bin/console $(cmd)

migrate-cmd:
	docker-compose exec app php bin/console app:migrate

seed-cmd:
	docker-compose exec app php bin/console app:seed

create-admin:
	@test -n "$(email)" || (echo "Usage: make create-admin email=admin@example.com password=secret name='Admin'" && exit 1)
	docker-compose exec app php bin/console app:create-admin "$(email)" "$(password)" "$(name)"

clear-cache:
	docker-compose exec app php bin/console app:clear-cache

generate-sitemap:
	docker-compose exec app php bin/console app:generate-sitemap

export-meetings:
	docker-compose exec app php bin/console app:export-meetings $(args)

# ─── GDPR cleanup ─────────────────────────────────────────────────────────────
cleanup:
	docker-compose exec app php bin/console app:cleanup

# ─── Help ─────────────────────────────────────────────────────────────────────
help:
	@echo ""
	@echo "  Mathex.cz – available make targets"
	@echo "  ────────────────────────────────────────────────────────────"
	@echo "  Docker"
	@echo "    make up                  Start all containers"
	@echo "    make down                Stop all containers"
	@echo "    make restart             Restart containers"
	@echo "    make build               Rebuild images (no cache)"
	@echo "    make shell               Shell in PHP container"
	@echo "    make shell-mysql         MySQL client"
	@echo "    make logs                Tail all logs"
	@echo ""
	@echo "  Composer"
	@echo "    make install             composer install"
	@echo "    make update              composer update"
	@echo ""
	@echo "  Migrations (Phinx)"
	@echo "    make migrate             Run pending migrations"
	@echo "    make phinx-rollback      Roll back last migration"
	@echo "    make phinx-status        Show migration status"
	@echo "    make phinx-create name=X Create new migration file"
	@echo ""
	@echo "  Seeders (Phinx)"
	@echo "    make seed                Run MainSeeder (all seeders)"
	@echo "    make phinx-seed-one name=X  Run a single seeder"
	@echo ""
	@echo "  Database helpers"
	@echo "    make db-reset            migrate + seed"
	@echo "    make db-fresh            rollback all + migrate + seed"
	@echo ""
	@echo "  CLI commands (bin/console)"
	@echo "    make migrate-cmd         app:migrate"
	@echo "    make seed-cmd            app:seed"
	@echo "    make create-admin email=.. password=.. name=..  app:create-admin"
	@echo "    make clear-cache         app:clear-cache"
	@echo "    make generate-sitemap    app:generate-sitemap"
	@echo "    make export-meetings [args='--from=2024-01-01 --output=out.csv']"
	@echo "    make cleanup             app:cleanup (GDPR retention)"
	@echo "    make console cmd='...'   Run any console command"
	@echo ""
	@echo "  Quality"
	@echo "    make stan                Run PHPStan"
	@echo "    make test                Run PHPUnit"
	@echo ""
