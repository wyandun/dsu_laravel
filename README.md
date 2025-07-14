# Sistema de Registro de Actividades Diarias - Laravel

Un sistema completo para el registro y gestión de actividades diarias desarrollado en Laravel 12 con integración a Active Directory.

## Características

### 🚀 Funcionalidades Principales

- **Sistema de Autenticación**: Integración con Active Directory (AD)
- **Estructura Jerárquica**:
  - **Coordinación de TICS** (extensible para más coordinaciones)
  - **4 Direcciones**: Seguridad, Infraestructura, Desarrollo de Soluciones, Gestión de Servicios Informáticos
- **Tres Tipos de Usuario**:
  - **Empleado**: Gestiona sus propias actividades diarias
  - **Director**: Supervisa actividades de empleados de su dirección específica
  - **Coordinador**: Supervisa actividades de todas las direcciones de su coordinación
- **Sistema de Reportes Avanzado**: Filtros por fechas, personal, dirección con exportación a Excel
- **Gestión de Actividades**: CRUD completo con validaciones
- **Restricciones Temporales**: Los empleados solo pueden editar actividades del día actual
- **Dashboard Diferenciado**: Vistas específicas según el rol y nivel jerárquico
- **Interfaz Moderna**: Diseño responsive con Tailwind CSS

### 📋 Formulario de Actividades

- **Título**: Descripción de la actividad
- **Tipo**: Quipux, Mantis, CTIT, Correo, Otros
- **Número de Referencia**: Código de referencia opcional
- **Tiempo**: Horas dedicadas (formato decimal)
- **Observaciones**: Comentarios adicionales
- **Fecha de Actividad**: Fecha en que se realizó la actividad

### 📊 Sistema de Reportes

- **Filtros Avanzados**: Por empleado, dirección, fechas, tipo, búsqueda de texto
- **Agrupamiento**: Visualización agrupada por dirección y coordinación
- **Exportación**: Generación de reportes en Excel con todos los filtros aplicados
- **Permisos Jerárquicos**: Cada jefe ve solo las actividades bajo su supervisión
- **Estadísticas**: Total de actividades y horas por período

### 🏢 Estructura Organizacional

#### Coordinación de TICS
- **Direcciones**:
  - Dirección de Seguridad
  - Dirección de Infraestructura
  - Dirección de Desarrollo de Soluciones
  - Dirección de Gestión de Servicios Informáticos

#### Roles y Permisos
- **Coordinador**: Ve todas las actividades de las direcciones de su coordinación
- **Director**: Ve solo actividades de empleados de su dirección específica
- **Empleado**: Ve y gestiona solo sus propias actividades

## 🛠️ Tecnologías

- **Framework**: Laravel 12
- **Base de Datos**: SQLite (desarrollo)
- **Autenticación**: Active Directory (AD) - En desarrollo: Laravel Breeze
- **Frontend**: Blade Templates + Tailwind CSS + Alpine.js
- **Exportación**: Laravel Excel (Maatwebsite)
- **Roles**: Sistema personalizado jerárquico

## 📦 Instalación

### Requisitos

- Docker & Docker Compose
- Git
- Make (opcional, para comandos simplificados)

### 🚀 Despliegue Rápido con Docker

#### Desarrollo
```bash
# Clonar el repositorio
git clone <repository-url>
cd dsu_laravel

# Levantar entorno de desarrollo
make dev-up

# O sin Make:
docker-compose -f docker-compose.dev.yml up -d

# Instalar dependencias
make install

# Configurar base de datos
make fresh
```

#### Producción
```bash
# Clonar el repositorio
git clone <repository-url>
cd dsu_laravel

# Configurar variables de entorno
cp .env.production .env
# Editar .env con configuraciones específicas

# Desplegar en producción
make prod-up

# O usar el script de deploy
./scripts/deploy.sh production
```

### 🛠️ Comandos Disponibles

#### Desarrollo
- `make dev-up` - Levantar entorno de desarrollo
- `make dev-down` - Detener entorno de desarrollo
- `make dev-shell` - Acceder al contenedor
- `make install` - Instalar dependencias
- `make fresh` - Refrescar base de datos
- `make test` - Ejecutar tests

#### Producción
- `make prod-up` - Levantar entorno de producción
- `make prod-down` - Detener entorno de producción
- `make prod-shell` - Acceder al contenedor
- `make migrate-prod` - Ejecutar migraciones
- `make optimize` - Optimizar aplicación
- `make deploy` - Despliegue completo

#### Utilidades
- `make backup-db` - Backup de base de datos
- `make clean` - Limpiar Docker
- `make status` - Estado de contenedores
- `make health` - Verificar salud

### 📱 URLs de Acceso

#### Desarrollo
- **Aplicación**: http://localhost:8000
- **MailHog** (emails): http://localhost:8025

#### Producción
- **Aplicación**: http://localhost
- **Health Check**: http://localhost/health

### Pasos de Instalación Manual

Si prefieres no usar Docker:

1. **Instalar dependencias de PHP**:
   ```bash
   composer install
   ```

2. **Instalar dependencias de Node.js**:
   ```bash
   npm install
   ```

3. **Configurar variables de entorno**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Ejecutar migraciones y seeders**:
   ```bash
   php artisan migrate --seed
   ```

5. **Compilar assets**:
   ```bash
   npm run build
   ```

6. **Iniciar servidor de desarrollo**:
   ```bash
   php artisan serve
   ```

## � Autenticación

### Estado Actual (Desarrollo)
El sistema actualmente usa Laravel Breeze para desarrollo y pruebas. Los usuarios se crean mediante seeders.

### Integración Futura con Active Directory

El sistema está diseñado para integrarse con Active Directory empresarial. Los cambios necesarios incluirán:

1. **Configuración LDAP**: 
   - Instalación del paquete `Adldap2/Adldap2-Laravel`
   - Configuración de conexión al servidor AD

2. **Mapeo de Campos**:
   ```php
   // Ejemplo de mapeo AD -> Laravel
   'name' => $user->getDisplayName(),
   'email' => $user->getEmail(),
   'coordinacion' => $user->getDepartment(),
   'direccion' => $user->getDivision(),
   'tipo_jefe' => determinarTipoJefe($user->getTitle()),
   ```

3. **Middleware de Sincronización**:
   - Sincronización automática de datos de usuario
   - Actualización de roles basada en grupos AD

4. **Eliminación del Registro**:
   - ✅ Ya implementado: Rutas de registro deshabilitadas
   - Los usuarios se autenticarán directamente con credenciales AD

## �👥 Usuarios de Prueba (Para Desarrollo)

Después de ejecutar los seeders, tendrás acceso a estos usuarios de prueba:

### Coordinador de TICS
- **Email**: coordinador.tics@sistema.com
- **Password**: password
- **Permisos**: Ver actividades de todas las direcciones de TICS

### Directores
- **Seguridad**: director.seguridad@sistema.com
- **Infraestructura**: director.infraestructura@sistema.com
- **Desarrollo**: director.desarrollo@sistema.com
- **Servicios**: director.servicios@sistema.com
- **Password**: password (todos)
- **Permisos**: Ver actividades solo de empleados de su dirección

### Empleados
- **Emails**: juan.perez@sistema.com, maria.garcia@sistema.com, etc.
- **Password**: password
- **Permisos**: Gestionar solo sus propias actividades

## 🎯 Funcionalidades por Rol

### Empleado
- ✅ Crear nuevas actividades
- ✅ Ver sus propias actividades
- ✅ Editar actividades del día actual
- ✅ Eliminar actividades del día actual
- ❌ Ver actividades de otros empleados

### Director
- ✅ Ver actividades de empleados de su dirección
- ✅ Generar reportes filtrados de su dirección
- ✅ Exportar reportes a Excel
- ✅ Crear sus propias actividades
- ✅ Dashboard con estadísticas de su equipo

### Coordinador
- ✅ Ver actividades de todas las direcciones de su coordinación
- ✅ Generar reportes completos de la coordinación
- ✅ Exportar reportes a Excel con todos los filtros
- ✅ Crear sus propias actividades
- ✅ Dashboard con estadísticas generales de toda la coordinación

## 📊 Dashboard

### Dashboard del Empleado
- Actividades registradas hoy
- Tiempo total trabajado hoy
- Estadísticas del mes actual
- Actividades recientes

### Dashboard del Director
- Estadísticas de empleados de su dirección
- Actividades del día de su equipo
- Tiempo total trabajado por su dirección
- Resumen de actividades recientes de la dirección

### Dashboard del Coordinador
- Total de empleados de toda la coordinación
- Actividades del día de todas las direcciones
- Tiempo total trabajado por coordinación
- Resumen de actividades por dirección

## 📈 Sistema de Reportes

### Filtros Disponibles
- **Por Empleado**: Selección individual (respeta jerarquía)
- **Por Dirección**: Filtrado por dirección específica
- **Por Fechas**: Rango de fechas flexible
- **Por Tipo**: Quipux, Mantis, CTIT, Correo, Otros
- **Búsqueda**: Texto libre en título, referencia, observaciones

### Visualización
- **Agrupamiento por Dirección**: Las actividades se agrupan por dirección
- **Estadísticas**: Total de actividades y horas por grupo
- **Paginación**: Navegación eficiente de resultados
- **Responsive**: Adaptado para móviles y tablets

### Exportación
- **Formato Excel**: Exportación completa con formato profesional
- **Filtros Aplicados**: El Excel respeta todos los filtros activos
- **Datos Completos**: Incluye fecha, empleado, coordinación, dirección, detalles

## 🔐 Seguridad

- Middleware de autenticación en todas las rutas protegidas
- Validación de permisos por rol y jerarquía
- Filtrado automático según nivel de supervisión
- Restricciones temporales para edición (empleados)
- Protección CSRF en todos los formularios
- Validación tanto en frontend como backend

## 🌐 API y Rutas

### Rutas Principales
- `/dashboard` - Dashboard principal (diferenciado por rol)
- `/activities` - Listado de actividades
- `/activities/create` - Crear nueva actividad
- `/activities/{id}` - Ver detalle de actividad
- `/activities/{id}/edit` - Editar actividad

### Middleware Aplicado
- `auth` - Autenticación requerida
- `verified` - Email verificado
- `role` - Control de acceso por rol

## 🎨 Diseño y UX

- **Responsive Design**: Adaptable a todos los dispositivos
- **Tailwind CSS**: Framework CSS moderno
- **Componentes Blade**: Reutilización de código
- **Mensajes de Estado**: Feedback claro al usuario
- **Validación en Tiempo Real**: Mejor experiencia de usuario

## 🔧 Desarrollo

### Estructura del Proyecto
```
app/
├── Http/Controllers/
│   ├── ActivityController.php
│   ├── DashboardController.php
│   └── Auth/
├── Models/
│   ├── User.php
│   └── Activity.php
└── Http/Middleware/
    └── RoleMiddleware.php

resources/views/
├── activities/
├── dashboard/
├── auth/
└── layouts/

database/
├── migrations/
└── seeders/
```

### Comandos Útiles

```bash
# Crear nueva migración
php artisan make:migration nombre_migracion

# Crear nuevo controlador
php artisan make:controller NombreController

# Ejecutar tests
php artisan test

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 📝 Próximas Mejoras

- [ ] Exportación de reportes en PDF/Excel
- [ ] Filtros avanzados de búsqueda
- [ ] Notificaciones en tiempo real
- [ ] API REST completa
- [ ] Sistema de comentarios en actividades
- [ ] Gestión de equipos y departamentos

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

Desarrollado como parte del sistema de gestión de actividades diarias.

---

**¡El sistema está listo para usarse! 🚀**

Accede a `http://localhost:8000` para comenzar a utilizarlo.
