.PHONY: up down logs test test-backend test-backend-coverage test-frontend test-frontend-coverage test-acceptance init build-frontend

# ──────────────────────────────────────────────
# Docker Compose
# ──────────────────────────────────────────────

up:
	docker compose up -d && docker compose logs -f

down:
	docker compose down

logs:
	docker compose logs -f

init:
	docker compose build --no-cache && cd frontend && npm install && cd .. && cd backend && cp .env.example .env && composer install

build-frontend:
	docker compose build frontend

refresh: down
	docker compose down -v
	docker compose build --no-cache
	docker compose up -d

# ──────────────────────────────────────────────
# Testing
# ──────────────────────────────────────────────

test: test-backend test-frontend

test-backend:
	docker compose exec backend php artisan test

test-backend-coverage:
	docker compose exec backend php artisan test --coverage

test-acceptance:
	docker compose exec -e ACCEPTANCE_BASE_URL=http://nginx:80 backend ./vendor/bin/behat

test-frontend:
	docker compose exec frontend npm run test:run

test-frontend-coverage:
	docker compose exec frontend npm run test:coverage