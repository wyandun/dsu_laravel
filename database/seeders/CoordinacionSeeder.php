<?php

namespace Database\Seeders;

use App\Models\Coordinacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * CoordinacionSeeder
 * 
 * Pobla la tabla de coordinaciones con la estructura organizacional.
 */
class CoordinacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar coordinaciones existentes
        Coordinacion::truncate();

        // Crear Coordinación de TICS
        Coordinacion::create([
            'nombre' => 'Coordinación de TICS',
            'codigo' => 'TICS',
            'descripcion' => 'Coordinación de Tecnologías de la Información y Comunicaciones',
            'activa' => true,
        ]);

        // Aquí se pueden agregar más coordinaciones en el futuro
        // Por ejemplo:
        /*
        Coordinacion::create([
            'nombre' => 'Coordinación de Recursos Humanos',
            'codigo' => 'RRHH',
            'descripcion' => 'Coordinación de Gestión de Recursos Humanos',
            'activa' => true,
        ]);
        */
    }
}
