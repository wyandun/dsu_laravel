# Estructura de Migraciones Normalizada

Este documento describe la estructura final de migraciones del sistema de actividades diarias después de la normalización completa.

## Migraciones Finales

### 1. Laravel Base Migrations
- `0001_01_01_000000_create_users_table.php` - Tabla base de usuarios de Laravel
- `0001_01_01_000001_create_cache_table.php` - Cache de Laravel
- `0001_01_01_000002_create_jobs_table.php` - Jobs/Queue de Laravel

### 2. Estructura Organizacional Normalizada

#### `2025_07_14_210922_create_coordinaciones_table.php`
```php
Schema::create('coordinaciones', function (Blueprint $table) {
    $table->id();
    $table->string('nombre')->unique();
    $table->string('codigo')->unique(); // Código corto para identificación
    $table->text('descripcion')->nullable();
    $table->boolean('activa')->default(true);
    $table->timestamps();
});
```

#### `2025_07_14_210928_create_direcciones_table.php`
```php
Schema::create('direcciones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('coordinacion_id')->constrained('coordinaciones')->onDelete('cascade');
    $table->string('nombre')->unique();
    $table->string('codigo')->unique(); // Código corto para identificación
    $table->text('descripcion')->nullable();
    $table->boolean('activa')->default(true);
    $table->timestamps();
});
```

#### `2025_07_14_210941_add_fields_to_users_table.php`
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('role')->default('empleado'); // empleado, jefe, administrador
    $table->string('tipo_jefe')->nullable(); // director, coordinador (solo para role=jefe)
    $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->onDelete('set null');
});
```

#### `2025_07_14_210934_create_activities_table.php`
```php
Schema::create('activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('titulo');
    $table->string('tipo'); // Quipux, Mantis, CTIT, Correo, Otros
    $table->string('numero_referencia')->nullable();
    $table->decimal('tiempo', 5, 2); // Tiempo en horas con 2 decimales
    $table->text('observaciones')->nullable();
    $table->date('fecha_actividad');
    $table->timestamps();

    // Índices para mejorar performance
    $table->index(['user_id', 'fecha_actividad']);
    $table->index(['tipo', 'numero_referencia']);
    $table->index('fecha_actividad');
});
```

## Relaciones de Base de Datos

```
coordinaciones
├── id (PK)
├── nombre (UNIQUE)
├── codigo (UNIQUE)
├── descripcion
├── activa
└── timestamps

direcciones
├── id (PK)
├── coordinacion_id (FK → coordinaciones.id)
├── nombre (UNIQUE)
├── codigo (UNIQUE)
├── descripcion
├── activa
└── timestamps

users (base Laravel + campos adicionales)
├── id (PK)
├── name
├── email (UNIQUE)
├── password
├── role (empleado|jefe|administrador)
├── tipo_jefe (director|coordinador) - nullable
├── direccion_id (FK → direcciones.id) - nullable
└── timestamps

activities
├── id (PK)
├── user_id (FK → users.id)
├── titulo
├── tipo (enum via ActivityType)
├── numero_referencia - nullable
├── tiempo (decimal 5,2)
├── observaciones - nullable
├── fecha_actividad (date)
└── timestamps
```

## Integridad Referencial

- **Coordinaciones → Direcciones**: Cascade delete
- **Direcciones → Users**: Set null on delete
- **Users → Activities**: Cascade delete

## Índices de Performance

### Tabla activities
- `[user_id, fecha_actividad]` - Para consultas de actividades por usuario y fecha
- `[tipo, numero_referencia]` - Para reportes colaborativos
- `[fecha_actividad]` - Para filtros por fecha

## Ventajas de la Estructura Normalizada

1. **Sin Redundancia**: Los usuarios no almacenan coordinación directamente
2. **Integridad Referencial**: Cambios organizacionales se propagan automáticamente
3. **Flexibilidad**: Fácil reorganización de direcciones entre coordinaciones
4. **Performance**: Índices optimizados para consultas frecuentes
5. **Escalabilidad**: Preparado para múltiples coordinaciones
6. **Mantenimiento**: Estructura clara y fácil de mantener

## Migración desde Estructura Anterior

Si ya tienes datos en una estructura anterior:

1. Hacer backup de la base de datos
2. Ejecutar `php artisan migrate:rollback --step=20`
3. Eliminar migraciones antiguas
4. Ejecutar las nuevas migraciones: `php artisan migrate`
5. Ejecutar seeders: `php artisan db:seed`

## Comandos para Desarrollo

```bash
# Reset completo de base de datos
php artisan migrate:fresh --seed

# Solo seeders (sin reset de estructura)
php artisan db:seed

# Verificar estado de migraciones
php artisan migrate:status
```

## Integración con Active Directory

La estructura está preparada para sincronización con AD:
- `direccion_id` se mapeará desde atributos organizacionales de AD
- `role` y `tipo_jefe` se determinarán por grupos de AD
- Las tablas `coordinaciones` y `direcciones` pueden poblarse desde AD o mantenerse manualmente
