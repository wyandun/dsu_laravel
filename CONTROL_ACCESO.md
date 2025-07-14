# Control de Acceso Jerárquico - Sistema de Actividades Diarias

Este documento explica cómo funciona el sistema de control de acceso jerárquico implementado en el sistema de registro de actividades diarias.

## Estructura Organizacional Normalizada

El sistema utiliza una estructura de base de datos normalizada con las siguientes tablas:

### Coordinaciones
- `id`: Identificador único
- `nombre`: Nombre de la coordinación
- `codigo`: Código corto para identificación
- `descripcion`: Descripción opcional
- `activa`: Estado activo/inactivo

### Direcciones
- `id`: Identificador único
- `coordinacion_id`: Llave foránea a coordinaciones
- `nombre`: Nombre de la dirección
- `codigo`: Código corto para identificación
- `descripcion`: Descripción opcional
- `activa`: Estado activo/inactivo

### Usuarios
- Los usuarios están vinculados solo a una `direccion_id`
- La coordinación se obtiene a través de la relación `direccion -> coordinacion`
- Esto evita redundancia y mantiene la integridad referencial

```
Coordinación de TICS (TICS)
├── Dirección de Seguridad (DSEG)
├── Dirección de Infraestructura (DINF)
├── Dirección de Desarrollo de Soluciones (DDES)
└── Dirección de Gestión de Servicios Informáticos (DGSI)
```

## Roles y Permisos

### 1. Empleado
- **Acceso**: Solo a sus propias actividades
- **Permisos**: 
  - Crear actividades diarias
  - Ver sus actividades registradas
  - Editar/eliminar actividades SOLO del día actual
- **Restricciones**: No puede acceder a reportes

### 2. Director
- **Acceso**: Empleados de su dirección específica
- **Permisos**:
  - Ver todas las actividades de empleados de su dirección
  - Acceder a reportes individuales y colaborativos
  - Exportar reportes a Excel
- **Ejemplo**: El Director de Seguridad solo puede ver actividades de empleados asignados a "Dirección de Seguridad"

### 3. Coordinador
- **Acceso**: Empleados de toda su coordinación (todas las direcciones)
- **Permisos**:
  - Ver actividades de empleados de todas las direcciones bajo su coordinación
  - Acceder a reportes individuales y colaborativos
  - Exportar reportes a Excel
- **Ejemplo**: El Coordinador de TICS puede ver actividades de empleados de todas las direcciones

### 4. Administrador General
- **Acceso**: TODAS las actividades del sistema
- **Permisos**:
  - Ver actividades de todos los empleados sin restricciones
  - Acceder a todos los reportes
  - Exportar todos los datos
  - No tiene restricciones organizacionales

## Implementación Técnica

### Modelo User - Métodos de Control

```php
/**
 * Obtener empleados que puede supervisar este jefe
 */
public function getEmpleadosBajoSupervision()
{
    // Solo jefes y administradores pueden supervisar
    if (!$this->isJefe() && !$this->isAdministrador()) {
        return collect();
    }

    $query = User::where('role', 'empleado');

    if ($this->isAdministrador()) {
        // El administrador puede ver todos los empleados
        return $query->get();
    } elseif ($this->isDirector()) {
        // El director puede ver solo empleados de su dirección específica
        $query->where('direccion_id', $this->direccion_id);
    } elseif ($this->isCoordinador()) {
        // El coordinador puede ver empleados de todas las direcciones de su coordinación
        // Obtener todas las direcciones de la coordinación del coordinador
        if ($this->direccion_id) {
            $coordinacionId = $this->direccion->coordinacion_id;
            $direccionesIds = Direccion::where('coordinacion_id', $coordinacionId)->pluck('id');
            $query->whereIn('direccion_id', $direccionesIds);
        } else {
            // Fallback para coordinadores sin dirección específica
            $direccionesIds = Direccion::whereHas('coordinacion', function($q) {
                $q->where('nombre', $this->coordinacion);
            })->pluck('id');
            $query->whereIn('direccion_id', $direccionesIds);
        }
    }

    return $query->get();
}

/**
 * Relación con coordinación a través de dirección
 */
public function coordinacion()
{
    return $this->hasOneThrough(Coordinacion::class, Direccion::class, 'id', 'id', 'direccion_id', 'coordinacion_id');
}
```

### Controladores - Aplicación de Filtros

#### ReportController y CollaborativeReportController

```php
// Aplicar filtros jerárquicos solo si NO es administrador
if (!$user->isAdministrador()) {
    $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
    $query->whereIn('user_id', $empleadosIds);
}
```

### Middleware CheckReportAccess

```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    // Solo administradores y jefes pueden acceder a reportes
    if (!$user || (!$user->isAdministrador() && !$user->isJefe())) {
        abort(403, 'No tienes permisos para acceder a los reportes.');
    }

    return $next($request);
}
```

## Casos de Uso Ejemplos

### Caso 1: Director de Seguridad
**Usuario**: Juan Pérez - Director de Seguridad
**Acceso**: Solo empleados asignados a "Dirección de Seguridad"
**Resultado**: Ve actividades de 3 empleados específicos de su dirección

### Caso 2: Coordinador de TICS
**Usuario**: María García - Coordinadora de TICS
**Acceso**: Empleados de todas las direcciones bajo "Coordinación de TICS"
**Resultado**: Ve actividades de empleados de las 4 direcciones (Seguridad, Infraestructura, Desarrollo, Gestión)

### Caso 3: Administrador General
**Usuario**: Administrador General
**Acceso**: Todos los empleados del sistema
**Resultado**: Ve actividades de TODOS los empleados sin restricciones

## Flujo de Autorización

1. **Autenticación**: Usuario debe estar logueado
2. **Verificación de Rol**: Middleware verifica que sea jefe o administrador
3. **Aplicación de Filtros**: 
   - Si es administrador: Sin restricciones
   - Si es director: Solo su dirección
   - Si es coordinador: Toda su coordinación
4. **Renderizado**: Solo se muestran datos autorizados

## Seguridad

- **Filtros a Nivel de Query**: Los filtros se aplican directamente en las consultas de base de datos
- **Validación en Controladores**: Doble verificación de permisos en cada método
- **Middleware Protector**: Las rutas de reportes están protegidas por middleware
- **Sin Bypass**: No hay forma de acceder a datos no autorizados

## Exportaciones Excel

Las exportaciones respetan los mismos filtros jerárquicos:
- **Director**: Excel con solo empleados de su dirección
- **Coordinador**: Excel con empleados de su coordinación
- **Administrador**: Excel con todos los empleados

## Datos de Prueba

### Usuarios Creados en UserSeeder:

```php
// Administrador General (acceso total)
'email' => 'admin@sistema.com'

// Coordinador TICS (ve todas las direcciones)
'email' => 'coordinador.tics@sistema.com'

// Directores (ven solo su dirección)
'email' => 'director.seguridad@sistema.com'
'email' => 'director.infraestructura@sistema.com'
'email' => 'director.desarrollo@sistema.com'
'email' => 'director.servicios@sistema.com'
```

Todos los usuarios de prueba tienen password: `password`

## Estructura de Base de Datos Normalizada

### Ventajas de la Nueva Estructura

1. **Eliminación de Redundancia**: Los usuarios no almacenan `coordinacion_id` directamente, se obtiene a través de la dirección
2. **Integridad Referencial**: Cambios en la estructura organizacional se reflejan automáticamente
3. **Flexibilidad**: Fácil reorganización de direcciones entre coordinaciones
4. **Escalabilidad**: Preparado para múltiples coordinaciones y direcciones

### Relaciones de Base de Datos

```sql
-- Coordinaciones
coordinaciones (id, nombre, codigo, descripcion, activa)

-- Direcciones (pertenecen a una coordinación)
direcciones (id, coordinacion_id, nombre, codigo, descripcion, activa)
  FOREIGN KEY coordinacion_id REFERENCES coordinaciones(id)

-- Usuarios (pertenecen a una dirección)
users (id, ..., direccion_id)
  FOREIGN KEY direccion_id REFERENCES direcciones(id)

-- La coordinación del usuario se obtiene mediante:
-- user.direccion.coordinacion
```

## Integración con Active Directory (Futuro)

El sistema está preparado para integración con AD:
- Los campos `coordinacion`, `direccion` y `tipo_jefe` se mapearán desde atributos AD
- La estructura normalizada facilitará la sincronización con AD
- Solo cambiará la fuente de datos de usuarios, la lógica se mantiene
- Las tablas de coordinaciones y direcciones pueden poblarse desde AD o mantenerse manualmente
