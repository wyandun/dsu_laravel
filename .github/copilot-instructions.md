<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# Sistema de Registro de Actividades Diarias - Laravel

Este es un sistema para registro de actividades diarias desarrollado en Laravel 12 (última versión). El sistema cuenta con dos perfiles de usuario: empleado y jefe.

## Características del Sistema

### Perfiles de Usuario
- **Empleado**: Puede registrar y ver sus propias actividades diarias. Solo puede modificar actividades del día actual.
- **Jefe**: Puede ver todas las actividades de todos los empleados.

### Formulario de Actividades
El formulario incluye los siguientes campos:
- **Título**: Campo de texto para el título de la actividad
- **Tipo**: Select con opciones: Quipux, Mantis, CTIT, Correo, Otros
- **Número de Referencia**: Campo de texto para referencia
- **Tiempo**: Campo numérico flotante para registrar tiempo en horas
- **Observaciones**: Campo de texto largo para observaciones

### Funcionalidades Principales
- Autenticación con roles (empleado/jefe)
- CRUD de actividades diarias
- Restricción de edición solo para actividades del día actual (empleados)
- Dashboard diferenciado por roles
- Interfaz moderna y responsive

## Estructura Técnica
- **Framework**: Laravel 12
- **Base de datos**: SQLite (desarrollo)
- **Autenticación**: Laravel Breeze
- **Frontend**: Blade templates con Tailwind CSS
- **Roles**: Sistema de roles personalizado

## Instrucciones para Desarrollo
- Seguir las convenciones de Laravel
- Usar middleware para proteger rutas por roles
- Implementar validaciones tanto en frontend como backend
- Mantener código limpio y bien documentado
- Usar factories y seeders para datos de prueba
