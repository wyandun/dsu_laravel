<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empleados = User::where('role', 'empleado')->get();
        $tipos = ['Quipux', 'Mantis', 'CTIT', 'Correo', 'Otros'];

        foreach ($empleados as $empleado) {
            // Crear actividades para los últimos 7 días
            for ($i = 0; $i < 7; $i++) {
                $fecha = Carbon::today()->subDays($i);
                
                // Crear entre 1 y 3 actividades por día
                $numActividades = rand(1, 3);
                
                for ($j = 0; $j < $numActividades; $j++) {
                    Activity::create([
                        'user_id' => $empleado->id,
                        'titulo' => 'Actividad ' . ($j + 1) . ' del ' . $fecha->format('d/m/Y'),
                        'tipo' => $tipos[array_rand($tipos)],
                        'numero_referencia' => 'REF-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'tiempo' => rand(1, 8) + (rand(0, 99) / 100), // Entre 1.00 y 8.99 horas
                        'observaciones' => 'Observaciones de ejemplo para la actividad ' . ($j + 1),
                        'fecha_actividad' => $fecha,
                    ]);
                }
            }
        }
    }
}
