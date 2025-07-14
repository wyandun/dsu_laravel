<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use App\Enums\ActivityType;
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
        $tipos = ActivityType::toArray();

        // Referencias colaborativas para simular proyectos en equipo
        $referenciasColaborativas = [
            'PRY-2025-001' => [
                'titulo_base' => 'Implementación Sistema de Gestión Documental',
                'tipo' => 'Quipux',
                'participantes' => 4,
                'observaciones' => 'Desarrollo del nuevo sistema de gestión documental para la institución'
            ],
            'MNT-2025-015' => [
                'titulo_base' => 'Actualización Infraestructura de Red',
                'tipo' => 'Mantis',
                'participantes' => 3,
                'observaciones' => 'Mantenimiento y actualización de equipos de red'
            ],
            'CTIT-2025-003' => [
                'titulo_base' => 'Migración a la Nube',
                'tipo' => 'CTIT',
                'participantes' => 5,
                'observaciones' => 'Proceso de migración de servicios hacia infraestructura en la nube'
            ],
            'COR-2025-028' => [
                'titulo_base' => 'Capacitación Usuarios Sistema',
                'tipo' => 'Correo',
                'participantes' => 2,
                'observaciones' => 'Coordinación y ejecución de capacitaciones a usuarios finales'
            ],
            'OTR-2025-012' => [
                'titulo_base' => 'Auditoría Sistemas Información',
                'tipo' => 'Otros',
                'participantes' => 3,
                'observaciones' => 'Auditoría integral de los sistemas de información institucionales'
            ]
        ];

        // Crear actividades colaborativas
        foreach ($referenciasColaborativas as $numeroRef => $proyecto) {
            $participantes = $empleados->random($proyecto['participantes']);
            
            // Crear actividades durante los últimos 10 días para cada proyecto
            for ($dia = 0; $dia < 10; $dia++) {
                $fecha = Carbon::today()->subDays($dia);
                
                // Algunos días no todos los participantes trabajan
                $participantesDelDia = $participantes->random(rand(1, $proyecto['participantes']));
                
                foreach ($participantesDelDia as $participante) {
                    // Crear entre 1 y 2 actividades por participante por día
                    $numActividades = rand(1, 2);
                    
                    for ($i = 0; $i < $numActividades; $i++) {
                        $subfases = [
                            'Análisis de requerimientos',
                            'Desarrollo de funcionalidades',
                            'Pruebas y validación',
                            'Documentación técnica',
                            'Revisión y correcciones',
                            'Implementación',
                            'Capacitación',
                            'Seguimiento'
                        ];
                        
                        Activity::create([
                            'user_id' => $participante->id,
                            'titulo' => $proyecto['titulo_base'] . ' - ' . $subfases[array_rand($subfases)],
                            'tipo' => $proyecto['tipo'],
                            'numero_referencia' => $numeroRef,
                            'tiempo' => rand(2, 6) + (rand(0, 99) / 100), // Entre 2.00 y 6.99 horas
                            'observaciones' => $proyecto['observaciones'] . '. Trabajo realizado en ' . $subfases[array_rand($subfases)] . ' durante el día ' . $fecha->format('d/m/Y'),
                            'fecha_actividad' => $fecha,
                        ]);
                    }
                }
            }
        }

        // Crear algunas actividades individuales (no colaborativas)
        foreach ($empleados as $empleado) {
            // Crear actividades individuales para los últimos 7 días
            for ($i = 0; $i < 7; $i++) {
                $fecha = Carbon::today()->subDays($i);
                
                // Solo algunos días y algunos empleados
                if (rand(1, 3) == 1) { // 33% de probabilidad
                    $actividadesIndividuales = [
                        'Revisión de correos administrativos',
                        'Actualización de documentación personal',
                        'Reunión de coordinación',
                        'Capacitación individual',
                        'Soporte técnico puntual',
                        'Actividad administrativa'
                    ];
                    
                    Activity::create([
                        'user_id' => $empleado->id,
                        'titulo' => $actividadesIndividuales[array_rand($actividadesIndividuales)],
                        'tipo' => $tipos[array_rand($tipos)],
                        'numero_referencia' => rand(1, 10) > 7 ? 'IND-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) : null, // 30% tienen referencia
                        'tiempo' => rand(1, 4) + (rand(0, 99) / 100), // Entre 1.00 y 4.99 horas
                        'observaciones' => 'Actividad individual realizada el ' . $fecha->format('d/m/Y'),
                        'fecha_actividad' => $fecha,
                    ]);
                }
            }
        }

        // Crear algunas actividades con espacios en blanco para probar la validación
        $empleadoTest = $empleados->first();
        Activity::create([
            'user_id' => $empleadoTest->id,
            'titulo' => '  Título con espacios al inicio y final  ',
            'tipo' => 'Otros',
            'numero_referencia' => '  REF-TEST-001  ',
            'tiempo' => 2.5,
            'observaciones' => '  Observaciones con espacios para probar la validación automática  ',
            'fecha_actividad' => Carbon::today(),
        ]);
    }
}
