# Makefile para el proyecto DSU Laravel
.DEFAULT_GOAL := help

# Variables
DOCKER_COMPOSE = docker-compose
DOCKER_COMPOSE_DEV = docker-compose -f docker-compose.dev.yml
PROJECT_NAME = dsu_laravel

# Colores para output
GREEN = \033[0;32m
YELLOW = \033[1;33m
RED = \033[0;31m
NC = \033[0m # No Color

.PHONY: help build up down restart logs shell install migrate seed fresh test cache-clear

help: ## Mostrar esta ayuda
	@echo "$(GREEN)Comandos disponibles para DSU Laravel:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'

# ================================
# COMANDOS DE DESARROLLO
# ================================
dev-build: ## Construir contenedores para desarrollo
	@echo "$(GREEN)Construyendo contenedores para desarrollo...$(NC)"
	$(DOCKER_COMPOSE_DEV) build --no-cache

dev-up: ## Levantar entorno de desarrollo
	@echo "$(GREEN)Levantando entorno de desarrollo...$(NC)"
	$(DOCKER_COMPOSE_DEV) up -d
	@echo "$(GREEN)Aplicación disponible en: http://localhost:8000$(NC)"
	@echo "$(GREEN)MailHog disponible en: http://localhost:8025$(NC)"

dev-down: ## Detener entorno de desarrollo
	@echo "$(YELLOW)Deteniendo entorno de desarrollo...$(NC)"
	$(DOCKER_COMPOSE_DEV) down

dev-restart: ## Reiniciar entorno de desarrollo
	@echo "$(YELLOW)Reiniciando entorno de desarrollo...$(NC)"
	$(DOCKER_COMPOSE_DEV) restart

dev-logs: ## Ver logs del entorno de desarrollo
	$(DOCKER_COMPOSE_DEV) logs -f

dev-shell: ## Entrar al contenedor de desarrollo
	$(DOCKER_COMPOSE_DEV) exec app-dev sh

# ================================
# COMANDOS DE PRODUCCIÓN
# ================================
prod-build: ## Construir contenedores para producción
	@echo "$(GREEN)Construyendo contenedores para producción...$(NC)"
	$(DOCKER_COMPOSE) build --no-cache

prod-up: ## Levantar entorno de producción
	@echo "$(GREEN)Levantando entorno de producción...$(NC)"
	$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)Aplicación disponible en: http://localhost$(NC)"

prod-down: ## Detener entorno de producción
	@echo "$(YELLOW)Deteniendo entorno de producción...$(NC)"
	$(DOCKER_COMPOSE) down

prod-restart: ## Reiniciar entorno de producción
	@echo "$(YELLOW)Reiniciando entorno de producción...$(NC)"
	$(DOCKER_COMPOSE) restart

prod-logs: ## Ver logs del entorno de producción
	$(DOCKER_COMPOSE) logs -f

prod-shell: ## Entrar al contenedor de producción
	$(DOCKER_COMPOSE) exec app sh

# ================================
# COMANDOS DE APLICACIÓN
# ================================
install: ## Instalar dependencias en desarrollo
	@echo "$(GREEN)Instalando dependencias...$(NC)"
	$(DOCKER_COMPOSE_DEV) exec app-dev composer install
	$(DOCKER_COMPOSE_DEV) exec app-dev npm install

migrate: ## Ejecutar migraciones
	@echo "$(GREEN)Ejecutando migraciones...$(NC)"
	$(DOCKER_COMPOSE_DEV) exec app-dev php artisan migrate

migrate-prod: ## Ejecutar migraciones en producción
	@echo "$(GREEN)Ejecutando migraciones en producción...$(NC)"
	$(DOCKER_COMPOSE) exec app php artisan migrate --force

seed: ## Ejecutar seeders
	@echo "$(GREEN)Ejecutando seeders...$(NC)"
	$(DOCKER_COMPOSE_DEV) exec app-dev php artisan db:seed

fresh: ## Refrescar base de datos con seeders
	@echo "$(GREEN)Refrescando base de datos...$(NC)"
	$(DOCKER_COMPOSE_DEV) exec app-dev php artisan migrate:fresh --seed

test: ## Ejecutar tests
	@echo "$(GREEN)Ejecutando tests...$(NC)"
	$(DOCKER_COMPOSE_DEV) exec app-dev php artisan test

cache-clear: ## Limpiar caché
	@echo "$(GREEN)Limpiando caché...$(NC)"
	$(DOCKER_COMPOSE_DEV) exec app-dev php artisan cache:clear
	$(DOCKER_COMPOSE_DEV) exec app-dev php artisan config:clear
	$(DOCKER_COMPOSE_DEV) exec app-dev php artisan route:clear
	$(DOCKER_COMPOSE_DEV) exec app-dev php artisan view:clear

optimize: ## Optimizar aplicación para producción
	@echo "$(GREEN)Optimizando aplicación...$(NC)"
	$(DOCKER_COMPOSE) exec app php artisan config:cache
	$(DOCKER_COMPOSE) exec app php artisan route:cache
	$(DOCKER_COMPOSE) exec app php artisan view:cache
	$(DOCKER_COMPOSE) exec app php artisan optimize

# ================================
# COMANDOS DE UTILIDAD
# ================================
backup-db: ## Crear backup de la base de datos
	@echo "$(GREEN)Creando backup de la base de datos...$(NC)"
	docker cp $$($(DOCKER_COMPOSE) ps -q app):/var/www/html/database/database.sqlite ./backup_$$(date +%Y%m%d_%H%M%S).sqlite

restore-db: ## Restaurar base de datos desde backup (usar: make restore-db FILE=backup.sqlite)
	@echo "$(GREEN)Restaurando base de datos desde $(FILE)...$(NC)"
	docker cp $(FILE) $$($(DOCKER_COMPOSE) ps -q app):/var/www/html/database/database.sqlite

clean: ## Limpiar contenedores, imágenes y volúmenes no utilizados
	@echo "$(YELLOW)Limpiando Docker...$(NC)"
	docker system prune -f
	docker volume prune -f

clean-all: ## Limpiar todo (¡CUIDADO! Elimina también volúmenes con datos)
	@echo "$(RED)Limpiando todo Docker (incluyendo volúmenes)...$(NC)"
	docker system prune -a -f
	docker volume prune -f

status: ## Mostrar estado de los contenedores
	@echo "$(GREEN)Estado de los contenedores:$(NC)"
	@echo "$(YELLOW)Desarrollo:$(NC)"
	$(DOCKER_COMPOSE_DEV) ps
	@echo "$(YELLOW)Producción:$(NC)"
	$(DOCKER_COMPOSE) ps

health: ## Verificar salud de los servicios
	@echo "$(GREEN)Verificando salud de los servicios...$(NC)"
	@curl -f http://localhost/health || echo "$(RED)Servicio no disponible$(NC)"
	@curl -f http://localhost:8000/ || echo "$(RED)Desarrollo no disponible$(NC)"

# ================================
# COMANDOS DE DESPLIEGUE
# ================================
deploy: ## Desplegar en producción
	@echo "$(GREEN)Desplegando en producción...$(NC)"
	git pull
	$(DOCKER_COMPOSE) down
	$(DOCKER_COMPOSE) build --no-cache
	$(DOCKER_COMPOSE) up -d
	make optimize
	@echo "$(GREEN)Despliegue completado!$(NC)"

quick-deploy: ## Despliegue rápido (sin rebuild)
	@echo "$(GREEN)Despliegue rápido...$(NC)"
	git pull
	$(DOCKER_COMPOSE) restart
	make optimize
	@echo "$(GREEN)Despliegue rápido completado!$(NC)"
