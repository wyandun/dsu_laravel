<?php

namespace Database\Seeders;

use App\Models\Coordinacion;
use App\Models\Direccion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * DireccionSeeder
 * 
 * Pobla la tabla de direcciones con la estructura organizacional.
 */
class DireccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar direcciones existentes
        Direccion::truncate();

        // Obtener la coordinación de TICS
        $coordinacionTics = Coordinacion::where('codigo', 'TICS')->first();

        if (!$coordinacionTics) {
            throw new \Exception('Debe ejecutar CoordinacionSeeder antes que DireccionSeeder');
        }

        // Crear direcciones bajo la Coordinación de TICS
        $direcciones = [
            [
                'nombre' => 'Dirección de Seguridad',
                'codigo' => 'DSEG',
                'descripcion' => 'Dirección encargada de la seguridad informática y protección de datos',
            ],
            [
                'nombre' => 'Dirección de Infraestructura',
                'codigo' => 'DINF',
                'descripcion' => 'Dirección encargada de la infraestructura tecnológica y redes',
            ],
            [
                'nombre' => 'Dirección de Desarrollo de Soluciones',
                'codigo' => 'DDES',
                'descripcion' => 'Dirección encargada del desarrollo de software y soluciones tecnológicas',
            ],
            [
                'nombre' => 'Dirección de Gestión de Servicios Informáticos',
                'codigo' => 'DGSI',
                'descripcion' => 'Dirección encargada de la gestión y soporte de servicios informáticos',
            ],
        ];

        foreach ($direcciones as $direccionData) {
            Direccion::create([
                'coordinacion_id' => $coordinacionTics->id,
                'nombre' => $direccionData['nombre'],
                'codigo' => $direccionData['codigo'],
                'descripcion' => $direccionData['descripcion'],
                'activa' => true,
            ]);
        }
    }
}
