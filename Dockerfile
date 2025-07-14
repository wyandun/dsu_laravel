# Dockerfile multi-stage para optimización de producción
FROM php:8.2-fpm-alpine AS base

# Instalar dependencias del sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libxml2-dev \
    sqlite \
    sqlite-dev \
    nodejs \
    npm

# Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar usuario no-root
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Directorio de trabajo
WORKDIR /var/www/html

# ================================
# STAGE: Desarrollo
# ================================
FROM base AS development

# Instalar Xdebug para desarrollo
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configuración PHP para desarrollo
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/99-custom.ini

# Exponer puerto para desarrollo
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ================================
# STAGE: Build assets
# ================================
FROM node:18-alpine AS assets

WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

COPY . .
RUN npm run build

# ================================
# STAGE: Composer dependencies
# ================================
FROM base AS vendor

WORKDIR /app
COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# ================================
# STAGE: Producción
# ================================
FROM base AS production

# Copiar configuraciones
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar aplicación
COPY --chown=www:www . /var/www/html

# Copiar dependencias de Composer optimizadas
COPY --from=vendor --chown=www:www /app/vendor /var/www/html/vendor

# Copiar assets compilados
COPY --from=assets --chown=www:www /app/public/build /var/www/html/public/build

# Crear directorios necesarios
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/bootstrap/cache \
    /run/nginx \
    /var/log/supervisor

# Establecer permisos
RUN chown -R www:www /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /run/nginx

# Optimizaciones Laravel
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

# Cambiar a usuario no-root
USER www

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
