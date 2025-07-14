<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Coordinacion;
use App\Models\Direccion;
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

        // Obtener referencias a coordinaciones y direcciones
        $coordinacionTics = Coordinacion::where('codigo', 'TICS')->first();
        
        $direccionSeguridad = Direccion::where('codigo', 'DSEG')->first();
        $direccionInfraestructura = Direccion::where('codigo', 'DINF')->first();
        $direccionDesarrollo = Direccion::where('codigo', 'DDES')->first();
        $direccionServicios = Direccion::where('codigo', 'DGSI')->first();

        if (!$coordinacionTics || !$direccionSeguridad || !$direccionInfraestructura || !$direccionDesarrollo || !$direccionServicios) {
            throw new \Exception('Debe ejecutar CoordinacionSeeder y DireccionSeeder antes que UserSeeder');
        }

        // Crear administrador general del sistema
        User::create([
            'name' => 'Administrador General',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'administrador',
            'tipo_jefe' => null,
            'direccion_id' => null,
        ]);

        // Crear coordinador de TICS (sin dirección específica)
        User::create([
            'name' => 'Coordinador TICS',
            'email' => 'coordinador.tics@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'coordinador',
            'direccion_id' => null, // El coordinador supervisa todas las direcciones de la coordinación
        ]);

        // Crear directores de cada dirección
        User::create([
            'name' => 'Director de Seguridad',
            'email' => 'director.seguridad@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'direccion_id' => $direccionSeguridad->id,
        ]);

        User::create([
            'name' => 'Director de Infraestructura',
            'email' => 'director.infraestructura@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'direccion_id' => $direccionInfraestructura->id,
        ]);

        User::create([
            'name' => 'Director de Desarrollo de Soluciones',
            'email' => 'director.desarrollo@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'direccion_id' => $direccionDesarrollo->id,
        ]);

        User::create([
            'name' => 'Director de Gestión de Servicios',
            'email' => 'director.servicios@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'direccion_id' => $direccionServicios->id,
        ]);

        // Crear empleados de Dirección de Seguridad
        User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'direccion_id' => $direccionSeguridad->id,
        ]);

        User::create([
            'name' => 'Ana Martínez',
            'email' => 'ana.martinez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'direccion_id' => $direccionSeguridad->id,
        ]);

        // Crear empleados de Dirección de Infraestructura
        User::create([
            'name' => 'Carlos López',
            'email' => 'carlos.lopez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'direccion_id' => $direccionInfraestructura->id,
        ]);

        User::create([
            'name' => 'María García',
            'email' => 'maria.garcia@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'direccion_id' => $direccionInfraestructura->id,
        ]);

        // Crear empleados de Dirección de Desarrollo de Soluciones
        User::create([
            'name' => 'Luis Rodríguez',
            'email' => 'luis.rodriguez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'direccion_id' => $direccionDesarrollo->id,
        ]);

        User::create([
            'name' => 'Carmen Sánchez',
            'email' => 'carmen.sanchez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'direccion_id' => $direccionDesarrollo->id,
        ]);

        // Crear empleados de Dirección de Gestión de Servicios
        User::create([
            'name' => 'Pedro Fernández',
            'email' => 'pedro.fernandez@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'direccion_id' => $direccionServicios->id,
        ]);

        User::create([
            'name' => 'Laura Torres',
            'email' => 'laura.torres@sistema.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'direccion_id' => $direccionServicios->id,
        ]);
    }
}
