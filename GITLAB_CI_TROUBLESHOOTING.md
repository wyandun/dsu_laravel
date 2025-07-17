# Troubleshooting GitLab CI/CD

## ❌ Error SSL Crítico - SERCOP

### Problema Actual:
```
curl: (77) error setting certificate file: /etc/gitlab-runner/certs/gitlab.sercop.gob.ec.crt
```

**Causa**: GitLab Runner está configurado con certificados SSL corporativos que fallan.

## 🚀 SOLUCIONES DISPONIBLES

### Opción 1: Sin SSL (RECOMENDADO para SERCOP)
```bash
mv .gitlab-ci.yml .gitlab-ci-backup.yml
mv .gitlab-ci-no-ssl.yml .gitlab-ci.yml
```
- ✅ Deshabilita completamente SSL/TLS
- ✅ Múltiples métodos de instalación de Composer
- ✅ Configuración específica para entornos corporativos

### Opción 2: Imagen con Composer Preinstalado
```bash
mv .gitlab-ci.yml .gitlab-ci-backup.yml
mv .gitlab-ci-composer-image.yml .gitlab-ci.yml
```
- ✅ Usa `composer:2.6` image
- ✅ No necesita instalar Composer
- ✅ Más rápido

### Opción 3: Imagen Laravel Oficial
```bash
mv .gitlab-ci.yml .gitlab-ci-backup.yml
mv .gitlab-ci-laravel-image.yml .gitlab-ci.yml
```
- ✅ Usa `laravelsail/php82-composer:latest`
- ✅ Todo preconfigurado para Laravel

### Opción 4: Descarga Manual de Composer
```bash
mv .gitlab-ci.yml .gitlab-ci-backup.yml
mv .gitlab-ci-manual-composer.yml .gitlab-ci.yml
```
- ✅ Descarga Composer desde GitHub releases
- ✅ No usa repositorio oficial

## 📁 Archivos de Configuración Disponibles

1. **`.gitlab-ci-no-ssl.yml`** - ⭐ **RECOMENDADO PARA SERCOP**
2. **`.gitlab-ci-composer-image.yml`** - Imagen Composer
3. **`.gitlab-ci-laravel-image.yml`** - Imagen Laravel Sail  
4. **`.gitlab-ci-manual-composer.yml`** - Descarga manual
5. **`.gitlab-ci-final.yml`** - Configuración optimizada (puede fallar SSL)
6. **`.gitlab-ci.yml`** - Configuración completa (problemas SSL)

## 🔧 Configuración SSL para SERCOP

La opción **sin SSL** incluye:
```yaml
variables:
  GIT_SSL_NO_VERIFY: "true"
  CURL_CA_BUNDLE: ""
  SSL_VERIFY: "false"
  COMPOSER_DISABLE_TLS: "true"

before_script:
  - rm -f /etc/gitlab-runner/certs/gitlab.sercop.gob.ec.crt || true
  - echo "insecure" >> ~/.curlrc || true
  - composer config --global disable-tls true
```

## ⚡ Solución Inmediata

**Para resolver AHORA:**
```bash
# Usar configuración sin SSL
mv .gitlab-ci.yml .gitlab-ci-backup.yml
mv .gitlab-ci-no-ssl.yml .gitlab-ci.yml
git add .gitlab-ci.yml
git commit -m "Fix: Configuración SSL para entorno SERCOP"
git push
```

## 🧪 Verificación Local

```bash
# Verificar que funciona sin SSL
composer config --global disable-tls true
composer config --global secure-http false
composer install
php artisan test
```

## 📊 Estado de Configuraciones

- 🔴 `.gitlab-ci.yml` - Falla por SSL de SERCOP
- 🟢 `.gitlab-ci-no-ssl.yml` - ✅ Soluciona problema SSL
- 🟢 `.gitlab-ci-composer-image.yml` - ✅ Evita instalación Composer
- 🟢 `.gitlab-ci-laravel-image.yml` - ✅ Todo preconfigurado
- 🟡 `.gitlab-ci-manual-composer.yml` - ✅ Descarga alternativa

## 🎯 Recomendación Final

**Para SERCOP:** Usar `.gitlab-ci-no-ssl.yml` porque:
- ✅ Maneja certificados corporativos
- ✅ Múltiples fallbacks para Composer
- ✅ Configuración SSL específica para entornos corporativos
- ✅ Jobs de emergencia incluidos

# GitLab CI/CD Troubleshooting

## Configuraciones Disponibles

Hemos creado múltiples configuraciones de GitLab CI para diferentes escenarios:

### 1. **`.gitlab-ci-production.yml`** (RECOMENDADO PARA PRODUCCIÓN)
- Configuración completa con 3 stages: prepare, build, test
- Manejo robusto de certificados SSL problemáticos
- Cache optimizado para mejorar velocidad
- Generación de todos los artefactos requeridos
- Perfecto para entornos corporativos con certificados internos

### 2. **`.gitlab-ci-ultra-simple.yml`** (PARA PRUEBAS RÁPIDAS)
- Un solo job que hace todo
- Mínima configuración
- Ideal para debugging rápido

### 3. **`.gitlab-ci-docker.yml`** (ALTERNATIVA CON DOCKER)
- Usa Docker Compose existente
- Evita problemas de dependencias del sistema
- Más aislado y reproducible

### 4. **`.gitlab-ci-no-ssl.yml`** (PARA SERCOP)
- Configuración específica para SERCOP
- Desactiva completamente verificación SSL
- Usa repositorios HTTP cuando sea posible

## Cómo Usar

Para usar cualquiera de estas configuraciones:

1. **Renombrar el archivo deseado a `.gitlab-ci.yml`:**
```bash
# Para producción (RECOMENDADO)
mv .gitlab-ci-production.yml .gitlab-ci.yml

# Para pruebas simples
mv .gitlab-ci-ultra-simple.yml .gitlab-ci.yml

# Para Docker
mv .gitlab-ci-docker.yml .gitlab-ci.yml
```

2. **Commit y push:**
```bash
git add .gitlab-ci.yml
git commit -m "Update GitLab CI configuration"
git push
```

## Problemas Conocidos y Soluciones

### 1. **Error de Certificados SSL**
```
curl: (60) SSL certificate problem: unable to get local issuer certificate
```

**Solución aplicada en todas las configuraciones:**
- `GIT_SSL_NO_VERIFY: "true"`
- `composer config --global secure-http false`
- `composer config --global disable-tls true`
- `npm config set strict-ssl false`

### 2. **Artefactos Faltantes**
```
vendor/, node_modules/, public/build/, .env not found
```

**Solución en `.gitlab-ci-production.yml`:**
- Job `prepare` dedicado para instalar dependencias
- Artifacts explícitos en cada stage
- Verificación de existencia de directorios

### 3. **Composer No Se Puede Instalar**
```
Failed to download composer
```

**Solución aplicada:**
- Descarga manual de Composer con `--disable-tls`
- Fallback con curl usando `-k` (insecure)
- Configuración global de Composer sin SSL

### 4. **NPM Registry Problems**
```
npm ERR! network request failed
```

**Solución aplicada:**
- `npm config set registry http://registry.npmjs.org/`
- `npm config set strict-ssl false`
- Cache de npm en `/tmp/npm-cache`

## Variables de Entorno Importantes

```yaml
variables:
  GIT_SSL_NO_VERIFY: "true"           # Desactiva verificación SSL Git
  COMPOSER_ALLOW_SUPERUSER: 1         # Permite Composer como root
  COMPOSER_NO_INTERACTION: 1          # No interactivo
  COMPOSER_CACHE_DIR: /tmp/composer-cache
  npm_config_cache: /tmp/npm-cache
  SSL_VERIFY: "false"                  # Desactiva SSL globalmente
  CURL_CA_BUNDLE: ""                   # Evita problemas de certificados
```

## Testing Local

Para probar localmente que el CI funcionará:

```bash
# Simular el ambiente CI
export GIT_SSL_NO_VERIFY=true
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_NO_INTERACTION=1

# Instalar Composer sin SSL (si es necesario)
curl -k -sS https://getcomposer.org/installer | php -- --disable-tls
sudo mv composer.phar /usr/local/bin/composer

# Configurar Composer
composer config --global secure-http false
composer config --global disable-tls true

# Probar instalación
composer install --no-scripts --prefer-dist
npm ci
npm run build
php artisan test
```

## Monitoreo del Pipeline

1. **Revisar logs detallados** en GitLab CI/CD → Pipelines
2. **Verificar artefactos** en cada job
3. **Comprobar cache** para optimización
4. **Validar que todos los directorios requeridos se generen**

## Recomendaciones Finales

1. **Para SERCOP:** Usar `.gitlab-ci-production.yml` o `.gitlab-ci-no-ssl.yml`
2. **Para desarrollo:** Usar `.gitlab-ci-ultra-simple.yml`
3. **Para ambientes Docker:** Usar `.gitlab-ci-docker.yml`
4. **Siempre validar** que los artefactos se generen correctamente
5. **Monitorear el tiempo** de ejecución del pipeline (objetivo: < 10 minutos)

## Contacto

Si persisten los problemas, revisar:
- Configuración de proxy en el servidor GitLab
- Certificados SSL corporativos
- Políticas de firewall
- Configuración de DNS interno

---

**Última actualización:** Configuraciones optimizadas para resolver problemas de SSL y artefactos faltantes en entornos corporativos.
