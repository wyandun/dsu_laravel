<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder - Datos de prueba para desarrollo
 * 
 * IMPORTANTE: Este seeder es temporal para desarrollo.
 * En producción, los usuarios se sincronizarán automáticamente desde Active Directory.
 * 
 * Estructura organizacional:
 * - Coordinación de TICS
 *   - Dirección de Seguridad
 *   - Dirección de Infraestructura  
 *   - Dirección de Desarrollo de Soluciones
 *   - Dirección de Gestión de Servicios Informáticos
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar usuarios existentes
        User::truncate();

        // Crear coordinador de TICS
        User::create([
            'name' => 'Coordinador TICS',
            'email' => 'coordinador.tics@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'coordinador',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => null, // El coordinador supervisa todas las direcciones
        ]);

        // Crear directores de cada dirección
        User::create([
            'name' => 'Director de Seguridad',
            'email' => 'director.seguridad@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Seguridad',
        ]);

        User::create([
            'name' => 'Director de Infraestructura',
            'email' => 'director.infraestructura@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Infraestructura',
        ]);

        User::create([
            'name' => 'Director de Desarrollo de Soluciones',
            'email' => 'director.desarrollo@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Desarrollo de Soluciones',
        ]);

        User::create([
            'name' => 'Director de Gestión de Servicios',
            'email' => 'director.servicios@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Gestión de Servicios Informáticos',
        ]);

        // Crear empleados de Dirección de Seguridad
        User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Seguridad',
        ]);

        User::create([
            'name' => 'Ana Martínez',
            'email' => 'ana.martinez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Seguridad',
        ]);

        // Crear empleados de Dirección de Infraestructura
        User::create([
            'name' => 'Carlos López',
            'email' => 'carlos.lopez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Infraestructura',
        ]);

        User::create([
            'name' => 'María García',
            'email' => 'maria.garcia@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Infraestructura',
        ]);

        // Crear empleados de Dirección de Desarrollo de Soluciones
        User::create([
            'name' => 'Luis Rodríguez',
            'email' => 'luis.rodriguez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Desarrollo de Soluciones',
        ]);

        User::create([
            'name' => 'Carmen Sánchez',
            'email' => 'carmen.sanchez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Desarrollo de Soluciones',
        ]);

        // Crear empleados de Dirección de Gestión de Servicios
        User::create([
            'name' => 'Pedro Fernández',
            'email' => 'pedro.fernandez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Gestión de Servicios Informáticos',
        ]);

        User::create([
            'name' => 'Laura Torres',
            'email' => 'laura.torres@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'coordinacion' => 'Coordinación de TICS',
            'direccion' => 'Dirección de Gestión de Servicios Informáticos',
        ]);
    }
}
