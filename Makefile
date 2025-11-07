# Makefile for Laravel Certificate Generator - Docker Commands
.PHONY: help

# Default environment (dev or prod)
ENV ?= dev
COMPOSE_FILE = docker-compose.yml
ifeq ($(ENV),prod)
    COMPOSE_FILE = compose.prod.yaml
endif

# Colors for terminal output
GREEN  := \033[0;32m
YELLOW := \033[0;33m
RED    := \033[0;31m
NC     := \033[0m # No Color

help: ## Show this help message
	@echo '${GREEN}Laravel Certificate Generator - Docker Commands${NC}'
	@echo ''
	@echo '${YELLOW}Usage:${NC}'
	@echo '  make [target] [ENV=dev|prod]'
	@echo ''
	@echo '${YELLOW}Available targets:${NC}'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  ${GREEN}%-20s${NC} %s\n", $$1, $$2}'

# Development Commands
dev: ## Start development environment
	@echo "${GREEN}Starting development environment...${NC}"
	docker compose up -d
	@echo "${GREEN}✓ Development environment started at http://localhost:8000${NC}"

dev-build: ## Build and start development environment
	@echo "${GREEN}Building development environment...${NC}"
	docker compose up -d --build
	@echo "${GREEN}✓ Development environment built and started${NC}"

dev-stop: ## Stop development environment
	@echo "${YELLOW}Stopping development environment...${NC}"
	docker compose down
	@echo "${GREEN}✓ Development environment stopped${NC}"

dev-restart: ## Restart development environment
	@echo "${YELLOW}Restarting development environment...${NC}"
	docker compose restart
	@echo "${GREEN}✓ Development environment restarted${NC}"

# Production Commands
prod: ## Start production environment
	@echo "${GREEN}Starting production environment...${NC}"
	docker compose -f compose.prod.yaml up -d
	@echo "${GREEN}✓ Production environment started${NC}"

prod-build: ## Build and start production environment
	@echo "${GREEN}Building production environment...${NC}"
	docker compose -f compose.prod.yaml up -d --build
	@echo "${GREEN}✓ Production environment built and started${NC}"

prod-stop: ## Stop production environment
	@echo "${YELLOW}Stopping production environment...${NC}"
	docker compose -f compose.prod.yaml down
	@echo "${GREEN}✓ Production environment stopped${NC}"

# Initial Setup
setup: ## Initial setup (copy .env, generate key, migrate, seed)
	@echo "${GREEN}Running initial setup...${NC}"
	@if [ ! -f .env ]; then \
		echo "${YELLOW}Copying .env.example to .env...${NC}"; \
		cp .env.example .env; \
	else \
		echo "${YELLOW}.env file already exists, skipping...${NC}"; \
	fi
	@echo "${GREEN}Generating application key...${NC}"
	docker compose -f $(COMPOSE_FILE) run --rm php-fpm php artisan key:generate
	@echo "${GREEN}Running migrations...${NC}"
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan migrate --force
	@echo "${GREEN}Seeding database...${NC}"
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan db:seed --force
	@echo "${GREEN}Creating storage link...${NC}"
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan storage:link
	@echo "${GREEN}✓ Setup completed!${NC}"

# Database Commands
migrate: ## Run database migrations
	@echo "${GREEN}Running migrations...${NC}"
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan migrate --force
	@echo "${GREEN}✓ Migrations completed${NC}"

migrate-fresh: ## Drop all tables and re-run migrations
	@echo "${RED}Warning: This will drop all database tables!${NC}"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan migrate:fresh --force; \
		echo "${GREEN}✓ Database reset completed${NC}"; \
	fi

seed: ## Seed database
	@echo "${GREEN}Seeding database...${NC}"
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan db:seed --force
	@echo "${GREEN}✓ Seeding completed${NC}"

db-reset: ## Reset database (migrate:fresh + seed)
	@echo "${RED}Warning: This will drop all database tables!${NC}"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan migrate:fresh --seed --force; \
		echo "${GREEN}✓ Database reset and seeded${NC}"; \
	fi

# Cache Commands
cache-clear: ## Clear all Laravel caches
	@echo "${YELLOW}Clearing caches...${NC}"
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan cache:clear
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan config:clear
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan route:clear
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan view:clear
	@echo "${GREEN}✓ All caches cleared${NC}"

cache-optimize: ## Optimize caches for production
	@echo "${GREEN}Optimizing caches...${NC}"
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan config:cache
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan route:cache
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan view:cache
	@echo "${GREEN}✓ Caches optimized${NC}"

# Logs
logs: ## Show all container logs
	docker compose -f $(COMPOSE_FILE) logs -f

logs-php: ## Show PHP-FPM logs
	docker compose -f $(COMPOSE_FILE) logs -f php-fpm

logs-web: ## Show Nginx logs
	docker compose -f $(COMPOSE_FILE) logs -f web

logs-laravel: ## Show Laravel application logs
	docker compose -f $(COMPOSE_FILE) exec php-fpm tail -f /var/www/storage/logs/laravel.log

# Shell Access
shell: ## Access PHP-FPM container shell
	docker compose -f $(COMPOSE_FILE) exec php-fpm sh

shell-web: ## Access Nginx container shell
	docker compose -f $(COMPOSE_FILE) exec web sh

tinker: ## Open Laravel Tinker
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan tinker

# Dependencies
composer-install: ## Install Composer dependencies
	docker compose -f $(COMPOSE_FILE) exec php-fpm composer install

composer-update: ## Update Composer dependencies
	docker compose -f $(COMPOSE_FILE) exec php-fpm composer update

npm-install: ## Install NPM dependencies
	docker compose -f $(COMPOSE_FILE) exec node npm install

npm-build: ## Build assets
	docker compose -f $(COMPOSE_FILE) exec node npm run build

npm-dev: ## Start Vite dev server
	docker compose -f $(COMPOSE_FILE) exec node npm run dev

# Testing
test: ## Run PHPUnit tests
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan test

# Backup & Restore
backup-db: ## Backup SQLite database
	@mkdir -p backups
	docker compose -f $(COMPOSE_FILE) cp php-fpm:/var/www/database/database.sqlite ./backups/database-$(shell date +%Y%m%d-%H%M%S).sqlite
	@echo "${GREEN}✓ Database backed up to backups/${NC}"

backup-storage: ## Backup storage files
	@mkdir -p backups
	docker compose -f $(COMPOSE_FILE) cp php-fpm:/var/www/storage/app/public ./backups/storage-$(shell date +%Y%m%d-%H%M%S)
	@echo "${GREEN}✓ Storage backed up to backups/${NC}"

# Cleanup
clean: ## Remove all containers, volumes, and networks
	@echo "${RED}Warning: This will remove all containers, volumes, and data!${NC}"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker compose -f docker-compose.yml down -v; \
		docker compose -f compose.prod.yaml down -v; \
		echo "${GREEN}✓ Cleanup completed${NC}"; \
	fi

ps: ## Show running containers
	docker compose -f $(COMPOSE_FILE) ps

restart: ## Restart all containers
	docker compose -f $(COMPOSE_FILE) restart
	@echo "${GREEN}✓ Containers restarted${NC}"

# Queue Worker
queue-work: ## Start queue worker
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan queue:work

queue-restart: ## Restart queue workers
	docker compose -f $(COMPOSE_FILE) exec php-fpm php artisan queue:restart
	@echo "${GREEN}✓ Queue workers restarted${NC}"
