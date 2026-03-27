# ERP API Docker Makefile

.PHONY: help build up down restart logs shell migrate seed test clean prod-build prod-up prod-down

# Default target
help:
	@echo "ERP API Docker Commands:"
	@echo ""
	@echo "Development:"
	@echo "  build          - Build all Docker images"
	@echo "  up             - Start all services"
	@echo "  down           - Stop all services"
	@echo "  restart        - Restart all services"
	@echo "  logs [service] - Show logs (optional: service name)"
	@echo "  shell          - Enter API Gateway shell"
	@echo "  migrate        - Run database migrations"
	@echo "  seed           - Run database seeders"
	@echo "  test           - Run tests"
	@echo "  clean          - Clean Docker resources"
	@echo ""
	@echo "Production:"
	@echo "  prod-build     - Build production images"
	@echo "  prod-up        - Start production services"
	@echo "  prod-down      - Stop production services"
	@echo ""
	@echo "Utilities:"
	@echo "  status         - Show container status"
	@echo "  health         - Check service health"
	@echo "  backup         - Backup database"
	@echo ""

# Development commands
build:
	@echo "Building Docker images..."
	docker-compose build

up:
	@echo "Starting development services..."
	docker-compose up -d
	@echo "Services started. API available at http://localhost:8000"

down:
	@echo "Stopping services..."
	docker-compose down

restart:
	@echo "Restarting services..."
	docker-compose restart

logs:
	@if [ -n "$(service)" ]; then \
		echo "Showing logs for $(service)..."; \
		docker-compose logs -f $(service); \
	else \
		echo "Showing logs for all services..."; \
		docker-compose logs -f; \
	fi

shell:
	@echo "Entering API Gateway shell..."
	docker-compose exec api-gateway bash

migrate:
	@echo "Running database migrations..."
	docker-compose exec api-gateway php artisan migrate

seed:
	@echo "Running database seeders..."
	docker-compose exec api-gateway php artisan db:seed

test:
	@echo "Running tests..."
	docker-compose exec api-gateway php artisan test

clean:
	@echo "Cleaning Docker resources..."
	docker-compose down -v
	docker system prune -f
	docker volume prune -f

# Production commands
prod-build:
	@echo "Building production images..."
	docker-compose -f docker-compose.prod.yml build

prod-up:
	@echo "Starting production services..."
	docker-compose -f docker-compose.prod.yml up -d
	@echo "Production services started"

prod-down:
	@echo "Stopping production services..."
	docker-compose -f docker-compose.prod.yml down

# Utility commands
status:
	@echo "Container status:"
	docker-compose ps

health:
	@echo "Checking service health..."
	@curl -s http://localhost:8000/health || echo "API Gateway: DOWN"
	@curl -s http://localhost:9001/health || echo "Auth Service: DOWN"
	@curl -s http://localhost:9002/health || echo "Client Service: DOWN"
	@curl -s http://localhost:9003/health || echo "Product Service: DOWN"
	@curl -s http://localhost:9004/health || echo "User Service: DOWN"
	@curl -s http://localhost:9005/health || echo "Sale Service: DOWN"

backup:
	@echo "Creating database backup..."
	@mkdir -p backups
	@if docker-compose exec mysql mysqldump -u root -p erp_api_prod > backups/backup_$(shell date +%Y%m%d_%H%M%S).sql; then \
		echo "Backup created successfully"; \
	else \
		echo "Backup failed - MySQL container may not be running"; \
	fi

# Development workflow
setup: build up migrate seed
	@echo "Development environment setup complete!"
	@echo "API available at http://localhost:8000"

# Quick restart for development
dev: down up
	@echo "Development services restarted"

# Full rebuild
rebuild: clean build up
	@echo "Full rebuild complete"
