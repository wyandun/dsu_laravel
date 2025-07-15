<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Models\Direccion;
use App\Models\Coordinacion;
use App\Enums\ActivityType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CollaborativeReportTest extends TestCase
{
    use RefreshDatabase;

    protected $empleado;
    protected $jefe;
    protected $admin;
    protected $direccion;
    protected $coordinacion;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear estructuras organizacionales
        $this->coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación de Prueba',
            'codigo' => 'COORD-TEST-003',
            'descripcion' => 'Coordinación para tests'
        ]);

        $this->direccion = Direccion::create([
            'nombre' => 'Dirección de Prueba',
            'codigo' => 'DIR-TEST-003',
            'descripcion' => 'Dirección para tests',
            'coordinacion_id' => $this->coordinacion->id
        ]);

        // Crear usuarios de prueba
        $this->empleado = User::create([
            'name' => 'Empleado Test',
            'email' => 'empleado@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
        ]);

        $this->jefe = User::create([
            'name' => 'Jefe Test',
            'email' => 'jefe@test.com',
            'password' => bcrypt('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'coordinador',
            'direccion_id' => $this->direccion->id,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'administrador',
        ]);
    }

    /** @test */
    public function empleado_no_puede_acceder_reportes_colaborativos()
    {
        $this->actingAs($this->empleado);

        $response = $this->get(route('collaborative-reports.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function jefe_puede_acceder_reportes_colaborativos()
    {
        $this->actingAs($this->jefe);

        $response = $this->get(route('collaborative-reports.index'));
        $response->assertStatus(200);
        $response->assertViewIs('collaborative-reports.index');
    }

    /** @test */
    public function admin_puede_acceder_reportes_colaborativos()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('collaborative-reports.index'));
        $response->assertStatus(200);
        $response->assertViewIs('collaborative-reports.index');
    }

    /** @test */
    public function reportes_muestran_solo_actividades_colaborativas()
    {
        // Actividad sin número de referencia (no colaborativa)
        Activity::create([
            'titulo' => 'Actividad individual',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        // Actividad con número de referencia (colaborativa)
        Activity::create([
            'titulo' => 'Actividad colaborativa',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        $response = $this->get(route('collaborative-reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas('collaborativeGroups');
        
        $groups = $response->viewData('collaborativeGroups');
        $this->assertCount(1, $groups); // Solo debe mostrar la actividad colaborativa
    }

    /** @test */
    public function reportes_agrupan_actividades_por_tipo_y_referencia()
    {
        $empleado2 = User::create([
            'name' => 'Empleado 2',
            'email' => 'empleado2@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        // Actividades del mismo tipo y referencia
        Activity::create([
            'titulo' => 'Actividad 1',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad 2',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 1.5,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $empleado2->id
        ]);

        // Actividad diferente
        Activity::create([
            'titulo' => 'Actividad 3',
            'tipo' => ActivityType::HELPDESK->value,
            'numero_referencia' => 'HD-2024-001',
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        $response = $this->get(route('collaborative-reports.index'));

        $response->assertStatus(200);
        $groups = $response->viewData('collaborativeGroups');
        
        $this->assertCount(2, $groups); // Debe haber 2 grupos diferentes
        
        // Verificar estadísticas del primer grupo
        $grupo1 = $groups->first();
        $this->assertEquals(2, $grupo1->total_actividades); // 2 actividades en el grupo QX-2024-001
        $this->assertEquals(2, $grupo1->total_participantes); // 2 participantes diferentes
        $this->assertEquals(3.5, $grupo1->total_tiempo); // 2.0 + 1.5 = 3.5 horas
    }

    /** @test */
    public function filtro_por_numero_referencia_funciona()
    {
        Activity::create([
            'titulo' => 'Actividad QX',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad HD',
            'tipo' => ActivityType::HELPDESK->value,
            'numero_referencia' => 'HD-2024-001',
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        // Filtrar por QX
        $response = $this->get(route('collaborative-reports.index', ['numero_referencia' => 'QX-2024']));

        $response->assertStatus(200);
        $groups = $response->viewData('collaborativeGroups');
        
        $this->assertCount(1, $groups);
        $this->assertEquals('QX-2024-001', $groups->first()->numero_referencia);
    }

    /** @test */
    public function filtro_por_fechas_funciona()
    {
        Activity::create([
            'titulo' => 'Actividad hoy',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad ayer',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-002',
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::yesterday(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        // Filtrar solo actividades de hoy
        $response = $this->get(route('collaborative-reports.index', [
            'fecha_inicio' => Carbon::today()->format('Y-m-d'),
            'fecha_fin' => Carbon::today()->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $groups = $response->viewData('collaborativeGroups');
        
        $this->assertCount(1, $groups);
        $this->assertEquals('QX-2024-001', $groups->first()->numero_referencia);
    }

    /** @test */
    public function autocompletado_numero_referencia_funciona()
    {
        Activity::create([
            'titulo' => 'Actividad 1',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad 2',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-002',
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad 3',
            'tipo' => ActivityType::HELPDESK->value,
            'numero_referencia' => 'HD-2024-001',
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        // Buscar referencias que contengan "QX"
        $response = $this->get(route('collaborative-reports.autocomplete.referencia', ['q' => 'QX']));

        $response->assertStatus(200);
        $referencias = $response->json();
        
        $this->assertCount(2, $referencias);
        $this->assertContains('QX-2024-001', $referencias);
        $this->assertContains('QX-2024-002', $referencias);
        $this->assertNotContains('HD-2024-001', $referencias);
    }

    /** @test */
    public function grafico_horas_por_direccion_respeta_filtros()
    {
        Activity::create([
            'titulo' => 'Actividad QX',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad HD',
            'tipo' => ActivityType::HELPDESK->value,
            'numero_referencia' => 'HD-2024-001',
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        // Sin filtros - debe mostrar todas las actividades
        $response = $this->get(route('collaborative-reports.chart.hours-by-direction'));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(3.0, $data['total']); // 2.0 + 1.0

        // Con filtro por número de referencia
        $response = $this->get(route('collaborative-reports.chart.hours-by-direction', [
            'numero_referencia' => 'QX-2024'
        ]));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(2.0, $data['total']); // Solo la actividad QX
    }

    /** @test */
    public function grafico_horas_por_empleado_respeta_filtros()
    {
        $empleado2 = User::create([
            'name' => 'Empleado 2',
            'email' => 'empleado2@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        Activity::create([
            'titulo' => 'Actividad empleado 1',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad empleado 2',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-002',
            'tiempo' => 1.5,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $empleado2->id
        ]);

        $this->actingAs($this->jefe);

        // Sin filtros - debe mostrar todos los empleados
        $response = $this->get(route('collaborative-reports.chart.hours-by-employee'));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(3.5, $data['total']); // 2.0 + 1.5

        // Con filtro por número de referencia
        $response = $this->get(route('collaborative-reports.chart.hours-by-employee', [
            'numero_referencia' => 'QX-2024-001'
        ]));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(2.0, $data['total']); // Solo empleado 1
    }

    /** @test */
    public function estadisticas_generales_son_correctas()
    {
        $empleado2 = User::create([
            'name' => 'Empleado 2',
            'email' => 'empleado2@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        // Grupo colaborativo 1
        Activity::create([
            'titulo' => 'Actividad 1',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad 2',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 1.5,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $empleado2->id
        ]);

        // Grupo colaborativo 2
        Activity::create([
            'titulo' => 'Actividad 3',
            'tipo' => ActivityType::HELPDESK->value,
            'numero_referencia' => 'HD-2024-001',
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        $response = $this->get(route('collaborative-reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas(['totalGrupos', 'totalActividades', 'totalTiempo']);
        
        $totalGrupos = $response->viewData('totalGrupos');
        $totalActividades = $response->viewData('totalActividades');
        $totalTiempo = $response->viewData('totalTiempo');
        
        $this->assertEquals(2, $totalGrupos); // 2 grupos colaborativos
        $this->assertEquals(3, $totalActividades); // 3 actividades colaborativas
        $this->assertEquals(4.5, $totalTiempo); // 2.0 + 1.5 + 1.0 = 4.5 horas
    }

    /** @test */
    public function usuario_no_autenticado_no_puede_acceder_reportes()
    {
        $response = $this->get(route('collaborative-reports.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('collaborative-reports.chart.hours-by-direction'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('collaborative-reports.chart.hours-by-employee'));
        $response->assertRedirect(route('login'));
    }
}
