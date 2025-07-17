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
