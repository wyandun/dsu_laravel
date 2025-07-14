# 🐳 Guía de Docker para DSU Laravel

## Arquitectura de Contenedores

### Desarrollo
- **app-dev**: Aplicación Laravel con Xdebug
- **redis-dev**: Cache y sesiones 
- **mysql-dev**: Base de datos (opcional)
- **mailhog**: Captura de emails

### Producción
- **app**: Aplicación optimizada con Nginx + PHP-FPM
- **redis**: Cache y sesiones
- **mysql**: Base de datos (opcional)

## Comandos Rápidos

### Desarrollo
```bash
# Iniciar desarrollo
make dev-up

# Ver logs en tiempo real
make dev-logs

# Acceder al contenedor
make dev-shell

# Detener todo
make dev-down
```

### Producción
```bash
# Desplegar en producción
./scripts/deploy.sh production

# Ver estado
make status

# Ver logs
make prod-logs

# Backup de DB
make backup-db
```

## Configuración de Entornos

### Variables de Entorno

- **`.env.docker.dev`**: Configuración para desarrollo
- **`.env.production`**: Configuración para producción
- **`.env.ad.example`**: Ejemplo para Active Directory

### Personalización

1. **Puertos**: Modificar en docker-compose.yml
2. **Recursos**: Ajustar en Dockerfile
3. **Base de datos**: Cambiar SQLite por MySQL en .env

## Debugging

### Xdebug en VS Code

1. Instalar extensión PHP Debug
2. Configurar launch.json:

```json
{
    "name": "Listen for Xdebug (Docker)",
    "type": "php",
    "request": "launch",
    "port": 9003,
    "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
    }
}
```

### Logs
```bash
# Logs de aplicación
make dev-logs

# Logs específicos
docker-compose -f docker-compose.dev.yml logs app-dev

# Logs de Nginx
docker-compose logs app | grep nginx
```

## Optimización para Producción

### Imagen Multi-stage
- **Base**: Dependencias del sistema
- **Assets**: Compilación de frontend
- **Vendor**: Dependencias de Composer
- **Production**: Imagen final optimizada

### Optimizaciones Aplicadas
- Cache de opcodes PHP
- Assets minificados
- Composer optimizado
- Nginx con compresión gzip
- Supervisor para servicios múltiples

## Monitoreo y Salud

### Health Checks
```bash
# Verificar salud
make health

# Health check manual
curl http://localhost/health
```

### Métricas
- Logs estructurados
- Health checks automáticos
- Supervisord para procesos

## Backup y Restauración

### Backup Automático
```bash
# Crear backup
make backup-db

# Backup con timestamp
docker cp $(docker-compose ps -q app):/var/www/html/database/database.sqlite ./backup_$(date +%Y%m%d_%H%M%S).sqlite
```

### Restauración
```bash
# Restaurar desde backup
make restore-db FILE=backup_20231214_120000.sqlite
```

## CI/CD con GitHub Actions

### Workflow Automático
1. **Test**: Pruebas en cada push
2. **Build**: Construcción de imagen Docker
3. **Deploy**: Despliegue automático a producción

### Secretos Requeridos
- `DOCKER_USERNAME`: Usuario de Docker Hub
- `DOCKER_PASSWORD`: Token de Docker Hub
- `HOST`: IP del servidor de producción
- `USERNAME`: Usuario SSH
- `PRIVATE_KEY`: Llave privada SSH

## Resolución de Problemas

### Problemas Comunes

**Error de permisos**:
```bash
sudo chown -R $USER:$USER storage bootstrap/cache
```

**Puerto ocupado**:
```bash
# Cambiar puerto en docker-compose.yml
ports:
  - "8001:8000"  # En lugar de 8000:8000
```

**Base de datos bloqueada**:
```bash
# Recrear contenedores
make dev-down
make dev-up
```

### Limpieza
```bash
# Limpiar todo Docker
make clean-all

# Limpiar solo imágenes no usadas
make clean
```

## Escalabilidad

### Load Balancer
Para múltiples instancias, usar el perfil loadbalancer:
```bash
docker-compose --profile loadbalancer up -d
```

### Base de Datos Externa
Para producción, considera usar:
- Amazon RDS
- Google Cloud SQL
- Azure Database

### Redis Externo
Para alta disponibilidad:
- Redis Cluster
- Amazon ElastiCache
- Redis Cloud

## Seguridad

### Mejores Prácticas
- Usuario no-root en contenedores
- Secrets management con Docker Secrets
- Network isolation
- Security headers en Nginx
- Regular updates de imágenes base

### Variables Sensibles
```bash
# Usar Docker Secrets en producción
echo "password123" | docker secret create db_password -
```

## Recursos y Monitoreo

### Límites de Recursos
```yaml
# En docker-compose.yml
deploy:
  resources:
    limits:
      memory: 512M
      cpus: '0.5'
```

### Monitoring
- Docker stats para métricas básicas
- Prometheus + Grafana para métricas avanzadas
- ELK Stack para logs centralizados
