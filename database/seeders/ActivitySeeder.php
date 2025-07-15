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
            'HLP-2025-015' => [
                'titulo_base' => 'Soporte Técnico Mesa de Ayuda',
                'tipo' => 'Helpdesk',
                'participantes' => 3,
                'observaciones' => 'Atención y resolución de tickets de soporte técnico'
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
            'SGA-2025-012' => [
                'titulo_base' => 'Actualización Sistema Gestión Administrativa',
                'tipo' => 'SGA',
                'participantes' => 3,
                'observaciones' => 'Actualización y mejoras al sistema de gestión administrativa'
            ],
            'CTR-2025-045' => [
                'titulo_base' => 'Seguimiento Contratos TI',
                'tipo' => 'Contrato',
                'participantes' => 2,
                'observaciones' => 'Seguimiento y control de contratos de tecnología'
            ],
            'OFC-2025-067' => [
                'titulo_base' => 'Elaboración Oficios Técnicos',
                'tipo' => 'Oficio',
                'participantes' => 2,
                'observaciones' => 'Elaboración de oficios técnicos y documentos oficiales'
            ],
            'GPR-2025-089' => [
                'titulo_base' => 'Implementación GPR Digital',
                'tipo' => 'GPR',
                'participantes' => 4,
                'observaciones' => 'Implementación del sistema de Gestión por Resultados digital'
            ],
            'REU-2025-021' => [
                'titulo_base' => 'Reuniones Coordinación Técnica',
                'tipo' => 'Reunión',
                'participantes' => 3,
                'observaciones' => 'Reuniones de coordinación y planificación técnica'
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
