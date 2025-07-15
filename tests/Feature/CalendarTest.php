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

class CalendarTest extends TestCase
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
            'codigo' => 'COORD-TEST-004',
            'descripcion' => 'Coordinación para tests'
        ]);

        $this->direccion = Direccion::create([
            'nombre' => 'Dirección de Prueba',
            'codigo' => 'DIR-TEST-004',
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
    public function calendario_requiere_autenticacion()
    {
        $response = $this->get(route('calendar.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function empleado_puede_acceder_calendario()
    {
        $this->actingAs($this->empleado);

        $response = $this->get(route('calendar.index'));

        $response->assertStatus(200);
        $response->assertViewIs('calendar.index');
    }

    /** @test */
    public function jefe_puede_acceder_calendario()
    {
        $this->actingAs($this->jefe);

        $response = $this->get(route('calendar.index'));

        $response->assertStatus(200);
        $response->assertViewIs('calendar.index');
    }

    /** @test */
    public function calendario_muestra_mes_actual_por_defecto()
    {
        $this->actingAs($this->empleado);

        $response = $this->get(route('calendar.index'));

        $response->assertStatus(200);
        $response->assertViewHas(['month', 'year']);
        
        $month = $response->viewData('month');
        $year = $response->viewData('year');
        
        $this->assertEquals(Carbon::now()->month, $month->month);
        $this->assertEquals(Carbon::now()->year, $year);
    }

    /** @test */
    public function calendario_puede_navegar_meses()
    {
        $this->actingAs($this->empleado);

        // Navegar a enero 2024
        $response = $this->get(route('calendar.index', ['month' => 1, 'year' => 2024]));

        $response->assertStatus(200);
        $response->assertViewHas(['month', 'year']);
        
        $month = $response->viewData('month');
        $year = $response->viewData('year');
        
        $this->assertEquals(1, $month->month);
        $this->assertEquals(2024, $year);
    }

    /** @test */
    public function empleado_ve_solo_sus_actividades_en_calendario()
    {
        $otroEmpleado = User::create([
            'name' => 'Otro Empleado',
            'email' => 'otro@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        // Actividad propia
        Activity::create([
            'titulo' => 'Mi actividad',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        // Actividad de otro empleado
        Activity::create([
            'titulo' => 'Actividad ajena',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $otroEmpleado->id
        ]);

        $this->actingAs($this->empleado);

        $response = $this->get(route('calendar.index'));

        $response->assertStatus(200);
        $response->assertViewHas('activitiesByDate');
        
        $activitiesByDate = $response->viewData('activitiesByDate');
        $today = Carbon::today()->format('Y-m-d');
        
        $this->assertArrayHasKey($today, $activitiesByDate);
        $this->assertCount(1, $activitiesByDate[$today]); // Solo su actividad
        $this->assertEquals('Mi actividad', $activitiesByDate[$today][0]->titulo);
    }

    /** @test */
    public function jefe_ve_actividades_de_empleados_bajo_supervision()
    {
        // Crear otro empleado en la misma dirección
        $empleadoBajoSupervision = User::create([
            'name' => 'Empleado Bajo Supervisión',
            'email' => 'empleado_supervision@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        // Crear otro empleado en dirección diferente
        $otraDireccion = Direccion::create([
            'nombre' => 'Otra Dirección',
            'codigo' => 'DIR-OTRA-001',
            'descripcion' => 'Otra dirección para tests',
            'coordinacion_id' => $this->coordinacion->id
        ]);

        $empleadoOtraDireccion = User::create([
            'name' => 'Empleado Otra Dirección',
            'email' => 'empleado_otra@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $otraDireccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        // Actividades
        Activity::create([
            'titulo' => 'Actividad del jefe',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->jefe->id
        ]);

        Activity::create([
            'titulo' => 'Actividad empleado bajo supervisión',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $empleadoBajoSupervision->id
        ]);

        Activity::create([
            'titulo' => 'Actividad empleado otra dirección',
            'tipo' => ActivityType::CORREO->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $empleadoOtraDireccion->id
        ]);

        $this->actingAs($this->jefe);

        $response = $this->get(route('calendar.index'));

        $response->assertStatus(200);
        $activitiesByDate = $response->viewData('activitiesByDate');
        $today = Carbon::today()->format('Y-m-d');
        
        $this->assertArrayHasKey($today, $activitiesByDate);
        $this->assertCount(2, $activitiesByDate[$today]); // Solo las de su dirección
        
        $titulos = collect($activitiesByDate[$today])->pluck('titulo')->toArray();
        $this->assertContains('Actividad del jefe', $titulos);
        $this->assertContains('Actividad empleado bajo supervisión', $titulos);
        $this->assertNotContains('Actividad empleado otra dirección', $titulos);
    }

    /** @test */
    public function admin_ve_todas_las_actividades()
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
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad empleado 2',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $empleado2->id
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('calendar.index'));

        $response->assertStatus(200);
        $activitiesByDate = $response->viewData('activitiesByDate');
        $today = Carbon::today()->format('Y-m-d');
        
        $this->assertArrayHasKey($today, $activitiesByDate);
        $this->assertCount(2, $activitiesByDate[$today]); // Ve todas las actividades
    }

    /** @test */
    public function autocompletado_empleados_funciona_para_jefe()
    {
        // Crear empleados en la misma dirección
        $empleado2 = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        $empleado3 = User::create([
            'name' => 'María García',
            'email' => 'maria@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        // Empleado en otra dirección
        $otraDireccion = Direccion::create([
            'nombre' => 'Otra Dirección',
            'codigo' => 'DIR-OTRA-002',
            'descripcion' => 'Otra dirección',
            'coordinacion_id' => $this->coordinacion->id
        ]);

        $empleadoOtraDireccion = User::create([
            'name' => 'Pedro López',
            'email' => 'pedro@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $otraDireccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        $this->actingAs($this->jefe);

        // Buscar empleados que contengan "emp"
        $response = $this->get(route('calendar.empleados', ['q' => 'emp']));

        $response->assertStatus(200);
        $empleados = $response->json();
        
        // Debe devolver empleados de su dirección que contengan "emp"
        $this->assertGreaterThan(0, count($empleados));
        
        $nombres = collect($empleados)->pluck('text')->toArray();
        $this->assertContains('Empleado Test', $nombres); // El empleado original
        
        // No debe incluir empleado de otra dirección
        $this->assertNotContains('Pedro López', $nombres);
    }

    /** @test */
    public function empleado_no_puede_usar_autocompletado_empleados()
    {
        $this->actingAs($this->empleado);

        $response = $this->get(route('calendar.empleados', ['q' => 'test']));

        $response->assertStatus(403);
    }

    /** @test */
    public function filtro_por_empleado_funciona_para_jefe()
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
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad empleado 2',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $empleado2->id
        ]);

        $this->actingAs($this->jefe);

        // Filtrar por empleado específico
        $response = $this->get(route('calendar.index', ['empleado_id' => $this->empleado->id]));

        $response->assertStatus(200);
        $activitiesByDate = $response->viewData('activitiesByDate');
        $today = Carbon::today()->format('Y-m-d');
        
        $this->assertArrayHasKey($today, $activitiesByDate);
        $this->assertCount(1, $activitiesByDate[$today]); // Solo actividades del empleado filtrado
        $this->assertEquals('Actividad empleado 1', $activitiesByDate[$today][0]->titulo);
    }

    /** @test */
    public function calendario_agrupa_actividades_por_fecha_correctamente()
    {
        // Crear actividades en diferentes fechas
        Activity::create([
            'titulo' => 'Actividad hoy',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad ayer',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::yesterday(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad hoy 2',
            'tipo' => ActivityType::CORREO->value,
            'tiempo' => 0.5,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->empleado);

        $response = $this->get(route('calendar.index'));

        $response->assertStatus(200);
        $activitiesByDate = $response->viewData('activitiesByDate');
        
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        
        // Verificar agrupación por fecha
        $this->assertArrayHasKey($today, $activitiesByDate);
        $this->assertArrayHasKey($yesterday, $activitiesByDate);
        
        $this->assertCount(2, $activitiesByDate[$today]); // 2 actividades hoy
        $this->assertCount(1, $activitiesByDate[$yesterday]); // 1 actividad ayer
        
        // Verificar títulos
        $titulosHoy = collect($activitiesByDate[$today])->pluck('titulo')->toArray();
        $this->assertContains('Actividad hoy', $titulosHoy);
        $this->assertContains('Actividad hoy 2', $titulosHoy);
    }

    /** @test */
    public function calendario_muestra_total_horas_por_dia()
    {
        Activity::create([
            'titulo' => 'Actividad 1',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 2.5,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad 2',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.5,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->empleado);

        $response = $this->get(route('calendar.index'));

        $response->assertStatus(200);
        $response->assertViewHas('dailyHours');
        
        $dailyHours = $response->viewData('dailyHours');
        $today = Carbon::today()->format('Y-m-d');
        
        $this->assertArrayHasKey($today, $dailyHours);
        $this->assertEquals(4.0, $dailyHours[$today]); // 2.5 + 1.5 = 4.0
    }
}
