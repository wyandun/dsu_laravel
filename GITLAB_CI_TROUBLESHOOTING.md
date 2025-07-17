# Troubleshooting GitLab CI/CD

## Problemas Comunes y Soluciones

### 1. Error de Artifacts "no matching files"

**Problema**: 
```
WARNING: vendor/: no matching files
WARNING: node_modules/: no matching files  
WARNING: public/build/: no matching files
```

**Causa**: Los directorios no se crearon durante el build.

**Soluciones**:

#### Opción A: Usar configuración simple
Renombra `.gitlab-ci-simple.yml` a `.gitlab-ci.yml`:
```bash
mv .gitlab-ci.yml .gitlab-ci-backup.yml
mv .gitlab-ci-simple.yml .gitlab-ci.yml
```

#### Opción B: Usar configuración mínima (solo PHP)
Renombra `.gitlab-ci-minimal.yml` a `.gitlab-ci.yml`:
```bash
mv .gitlab-ci.yml .gitlab-ci-backup.yml
mv .gitlab-ci-minimal.yml .gitlab-ci.yml
```

### 2. Error 503 Service Unavailable en repositorios

**Problema**: No se pueden descargar paquetes de Debian.

**Solución**: El pipeline ya está configurado con repositorios alternativos y timeouts.

### 3. Problemas SSL/TLS

**Problema**: Certificados no reconocidos.

**Solución**: Variables ya configuradas:
- `GIT_SSL_NO_VERIFY: "true"`
- `CURL_CA_BUNDLE: ""`
- `NODE_TLS_REJECT_UNAUTHORIZED: "0"`

### 4. Node.js no disponible

**Problema**: No se puede instalar Node.js.

**Solución**: Use la configuración mínima que solo usa PHP.

## Archivos de Configuración Disponibles

1. **`.gitlab-ci.yml`** - Configuración completa (puede fallar)
2. **`.gitlab-ci-simple.yml`** - Configuración robusta con fallbacks
3. **`.gitlab-ci-minimal.yml`** - Solo PHP, sin Node.js

## Scripts Disponibles

1. **`scripts/gitlab-build.sh`** - Script personalizado con logging detallado

## Comandos de Debug

### Verificar que funciona localmente:
```bash
composer install
php artisan key:generate
php artisan test
```

### Simular entorno CI:
```bash
# Crear entorno limpio
rm -rf vendor/ node_modules/ bootstrap/cache/*
cp .env.example .env

# Instalar dependencias
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan test
```

## Recomendaciones

1. **Para desarrollo rápido**: Use `.gitlab-ci-minimal.yml`
2. **Para producción**: Use `.gitlab-ci-simple.yml`
3. **Para debugging**: Use `.gitlab-ci.yml` con logs completos

## Variables de Entorno Requeridas en GitLab

- `MYSQL_DATABASE`: testing
- `MYSQL_ROOT_PASSWORD`: testing  
- `MYSQL_USER`: testing
- `MYSQL_PASSWORD`: testing
- `GIT_SSL_NO_VERIFY`: true

## Contacto

Si persisten los problemas, revisar logs completos del pipeline y usar la configuración mínima.
