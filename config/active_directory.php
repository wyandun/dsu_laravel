<?php

/**
 * Configuración para futura integración con Active Directory
 * 
 * Este archivo contiene la configuración base que se usará cuando se integre
 * el sistema con Active Directory empresarial.
 */

return [
    
    /*
    |--------------------------------------------------------------------------
    | Active Directory Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la conexión con Active Directory
    | Se habilitará en producción cuando se instale Adldap2/Adldap2-Laravel
    |
    */
    
    'enabled' => env('AD_ENABLED', false),
    
    'connection' => [
        'hosts' => [env('AD_HOST', 'your-domain-controller.company.com')],
        'base_dn' => env('AD_BASE_DN', 'dc=company,dc=com'),
        'username' => env('AD_USERNAME', 'service-account'),
        'password' => env('AD_PASSWORD', 'service-password'),
        'port' => env('AD_PORT', 389),
        'use_ssl' => env('AD_USE_SSL', false),
        'use_tls' => env('AD_USE_TLS', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | User Mapping
    |--------------------------------------------------------------------------
    |
    | Mapeo de campos entre Active Directory y el modelo User de Laravel
    |
    */
    
    'user_mapping' => [
        'name' => 'displayname',           // Nombre completo del usuario
        'email' => 'mail',                 // Email corporativo
        'coordinacion' => 'department',    // Departamento -> Coordinación
        'direccion' => 'division',         // División -> Dirección
        'employee_id' => 'employeeid',     // ID de empleado
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Role Mapping
    |--------------------------------------------------------------------------
    |
    | Determinación de roles basada en grupos de Active Directory
    | o atributos específicos del usuario
    |
    */
    
    'role_mapping' => [
        'jefe_groups' => [
            'CN=TICS-Coordinadores,OU=Groups,DC=company,DC=com',
            'CN=TICS-Directores,OU=Groups,DC=company,DC=com',
        ],
        'empleado_groups' => [
            'CN=TICS-Empleados,OU=Groups,DC=company,DC=com',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Hierarchy Detection
    |--------------------------------------------------------------------------
    |
    | Reglas para determinar el tipo de jefe basado en el título o grupos AD
    |
    */
    
    'hierarchy_rules' => [
        'coordinador_titles' => [
            'Coordinador de TICS',
            'Coordinador TI',
            'Coordinador de Tecnología',
        ],
        'director_titles' => [
            'Director de Seguridad',
            'Director de Infraestructura',
            'Director de Desarrollo',
            'Director de Servicios',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configuración para la sincronización de usuarios
    |
    */
    
    'sync' => [
        'enabled' => env('AD_SYNC_ENABLED', true),
        'on_login' => env('AD_SYNC_ON_LOGIN', true),
        'schedule' => env('AD_SYNC_SCHEDULE', 'daily'),
        'create_missing_users' => env('AD_CREATE_MISSING_USERS', true),
        'update_existing_users' => env('AD_UPDATE_EXISTING_USERS', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Organizational Units
    |--------------------------------------------------------------------------
    |
    | OUs específicas donde buscar usuarios del sistema
    |
    */
    
    'organizational_units' => [
        'tics' => 'OU=TICS,OU=Users,DC=company,DC=com',
    ],
    
];
