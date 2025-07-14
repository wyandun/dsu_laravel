#!/bin/bash

# Script de despliegue para producción
# Uso: ./scripts/deploy.sh [environment]

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables
ENVIRONMENT=${1:-production}
PROJECT_NAME="dsu_laravel"
BACKUP_DIR="backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo -e "${GREEN}🚀 Iniciando despliegue para $ENVIRONMENT...${NC}"

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: No se encontró el archivo artisan. Ejecuta desde el directorio raíz del proyecto.${NC}"
    exit 1
fi

# Crear directorio de backups si no existe
mkdir -p $BACKUP_DIR

# Función para hacer backup de la base de datos
backup_database() {
    echo -e "${YELLOW}📦 Creando backup de la base de datos...${NC}"
    
    if [ -f "database/database.sqlite" ]; then
        cp database/database.sqlite "$BACKUP_DIR/database_backup_$TIMESTAMP.sqlite"
        echo -e "${GREEN}✅ Backup creado: $BACKUP_DIR/database_backup_$TIMESTAMP.sqlite${NC}"
    else
        echo -e "${YELLOW}⚠️ No se encontró la base de datos SQLite para backup${NC}"
    fi
}

# Función para verificar servicios
check_services() {
    echo -e "${YELLOW}🔍 Verificando servicios...${NC}"
    
    # Verificar Docker
    if ! command -v docker &> /dev/null; then
        echo -e "${RED}❌ Docker no está instalado${NC}"
        exit 1
    fi
    
    # Verificar Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        echo -e "${RED}❌ Docker Compose no está instalado${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}✅ Servicios verificados${NC}"
}

# Función para despliegue en producción
deploy_production() {
    echo -e "${GREEN}🏭 Desplegando en producción...${NC}"
    
    # Backup antes del despliegue
    backup_database
    
    # Detener servicios actuales
    echo -e "${YELLOW}⏹️ Deteniendo servicios actuales...${NC}"
    docker-compose down || true
    
    # Construir nuevas imágenes
    echo -e "${YELLOW}🔨 Construyendo imágenes...${NC}"
    docker-compose build --no-cache
    
    # Levantar servicios
    echo -e "${YELLOW}🚀 Levantando servicios...${NC}"
    docker-compose up -d
    
    # Esperar a que los servicios estén listos
    echo -e "${YELLOW}⏳ Esperando servicios...${NC}"
    sleep 10
    
    # Ejecutar migraciones
    echo -e "${YELLOW}🗃️ Ejecutando migraciones...${NC}"
    docker-compose exec -T app php artisan migrate --force
    
    # Optimizar aplicación
    echo -e "${YELLOW}⚡ Optimizando aplicación...${NC}"
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    docker-compose exec -T app php artisan optimize
    
    # Verificar salud
    echo -e "${YELLOW}🏥 Verificando salud de la aplicación...${NC}"
    sleep 5
    
    if curl -f http://localhost/health &> /dev/null; then
        echo -e "${GREEN}✅ Aplicación funcionando correctamente${NC}"
    else
        echo -e "${RED}❌ Error: La aplicación no responde${NC}"
        exit 1
    fi
}

# Función para despliegue en desarrollo
deploy_development() {
    echo -e "${GREEN}🔧 Desplegando en desarrollo...${NC}"
    
    # Detener servicios actuales
    echo -e "${YELLOW}⏹️ Deteniendo servicios actuales...${NC}"
    docker-compose -f docker-compose.dev.yml down || true
    
    # Construir imágenes
    echo -e "${YELLOW}🔨 Construyendo imágenes...${NC}"
    docker-compose -f docker-compose.dev.yml build
    
    # Levantar servicios
    echo -e "${YELLOW}🚀 Levantando servicios...${NC}"
    docker-compose -f docker-compose.dev.yml up -d
    
    # Instalar dependencias
    echo -e "${YELLOW}📦 Instalando dependencias...${NC}"
    docker-compose -f docker-compose.dev.yml exec -T app-dev composer install
    docker-compose -f docker-compose.dev.yml exec -T app-dev npm install
    
    # Ejecutar migraciones y seeders
    echo -e "${YELLOW}🗃️ Configurando base de datos...${NC}"
    docker-compose -f docker-compose.dev.yml exec -T app-dev php artisan migrate --seed
    
    echo -e "${GREEN}✅ Desarrollo listo en http://localhost:8000${NC}"
}

# Función principal
main() {
    check_services
    
    case $ENVIRONMENT in
        "production"|"prod")
            deploy_production
            echo -e "${GREEN}🎉 Despliegue en producción completado!${NC}"
            echo -e "${GREEN}🌐 Aplicación disponible en: http://localhost${NC}"
            ;;
        "development"|"dev")
            deploy_development
            echo -e "${GREEN}🎉 Despliegue en desarrollo completado!${NC}"
            echo -e "${GREEN}🌐 Aplicación disponible en: http://localhost:8000${NC}"
            echo -e "${GREEN}📧 MailHog disponible en: http://localhost:8025${NC}"
            ;;
        *)
            echo -e "${RED}❌ Entorno no válido. Usa: production, prod, development, o dev${NC}"
            exit 1
            ;;
    esac
}

# Ejecutar función principal
main
