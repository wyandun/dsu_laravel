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

class DashboardTest extends TestCase
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
            'codigo' => 'COORD-TEST-002',
            'descripcion' => 'Coordinación para tests'
        ]);

        $this->direccion = Direccion::create([
            'nombre' => 'Dirección de Prueba',
            'codigo' => 'DIR-TEST-002',
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
    public function dashboard_requiere_autenticacion()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function empleado_puede_acceder_a_dashboard()
    {
        $this->actingAs($this->empleado);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.empleado');
    }

    /** @test */
    public function jefe_puede_acceder_a_dashboard()
    {
        $this->actingAs($this->jefe);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.jefe');
    }

    /** @test */
    public function admin_puede_acceder_a_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.jefe'); // Admin usa la vista de jefe
    }

    /** @test */
    public function dashboard_empleado_muestra_estadisticas_correctas()
    {
        // Crear actividades de prueba para el empleado
        Activity::create([
            'titulo' => 'Actividad hoy 1',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 2.5,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad hoy 2',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.5,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad este mes',
            'tipo' => ActivityType::CORREO->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::now()->startOfMonth()->addDays(10),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad mes pasado',
            'tipo' => ActivityType::OTROS->value,
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::now()->subMonth(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->empleado);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        
        // Verificar estadísticas
        $this->assertEquals(2, $stats['actividades_hoy']); // 2 actividades hoy
        $this->assertEquals(4.0, $stats['tiempo_hoy']); // 2.5 + 1.5 = 4.0 horas hoy
        $this->assertEquals(3, $stats['actividades_mes']); // 3 actividades en el mes actual
        $this->assertEquals(5.0, $stats['tiempo_mes']); // 2.5 + 1.5 + 1.0 = 5.0 horas este mes
    }

    /** @test */
    public function dashboard_jefe_muestra_estadisticas_generales()
    {
        // Crear algunos empleados y actividades
        $empleado2 = User::create([
            'name' => 'Empleado 2',
            'email' => 'empleado2@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        // Actividades de hoy
        Activity::create([
            'titulo' => 'Actividad empleado 1',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        Activity::create([
            'titulo' => 'Actividad empleado 2',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 3.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $empleado2->id
        ]);

        Activity::create([
            'titulo' => 'Actividad ayer',
            'tipo' => ActivityType::CORREO->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::yesterday(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        
        // Verificar estadísticas generales
        $this->assertEquals(3, $stats['total_empleados']); // empleado, empleado2, y empleado original
        $this->assertEquals(2, $stats['actividades_hoy']); // 2 actividades hoy
        $this->assertEquals(3, $stats['total_actividades']); // 3 actividades en total
        $this->assertEquals(5.0, $stats['tiempo_total_hoy']); // 2.0 + 3.0 = 5.0 horas hoy
    }

    /** @test */
    public function dashboard_empleado_muestra_actividades_recientes()
    {
        // Crear varias actividades
        $actividades = [];
        for ($i = 0; $i < 7; $i++) {
            $actividades[] = Activity::create([
                'titulo' => "Actividad $i",
                'tipo' => ActivityType::QUIPUX->value,
                'tiempo' => 1.0,
                'fecha_actividad' => Carbon::today()->subDays($i),
                'user_id' => $this->empleado->id
            ]);
        }

        $this->actingAs($this->empleado);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('actividades_recientes');
        
        $actividadesRecientes = $response->viewData('actividades_recientes');
        
        // Debe mostrar máximo 5 actividades recientes
        $this->assertCount(5, $actividadesRecientes);
        
        // Deben estar ordenadas por fecha descendente
        $this->assertEquals('Actividad 0', $actividadesRecientes[0]->titulo);
        $this->assertEquals('Actividad 4', $actividadesRecientes[4]->titulo);
    }

    /** @test */
    public function dashboard_jefe_muestra_actividades_recientes_de_todos()
    {
        $empleado2 = User::create([
            'name' => 'Empleado 2',
            'email' => 'empleado2@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        // Crear actividades de diferentes empleados
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

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('actividades_recientes');
        
        $actividadesRecientes = $response->viewData('actividades_recientes');
        
        // Debe mostrar actividades de todos los empleados
        $this->assertCount(2, $actividadesRecientes);
        
        // Verificar que incluye información del usuario
        $titles = $actividadesRecientes->pluck('titulo')->toArray();
        $this->assertContains('Actividad empleado 1', $titles);
        $this->assertContains('Actividad empleado 2', $titles);
    }

    /** @test */
    public function dashboard_empleado_no_muestra_actividades_de_otros()
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

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        
        $stats = $response->viewData('stats');
        $actividadesRecientes = $response->viewData('actividades_recientes');
        
        // Solo debe contar sus propias actividades
        $this->assertEquals(1, $stats['actividades_hoy']);
        $this->assertEquals(1.0, $stats['tiempo_hoy']);
        
        // Solo debe mostrar sus propias actividades recientes
        $this->assertCount(1, $actividadesRecientes);
        $this->assertEquals('Mi actividad', $actividadesRecientes[0]->titulo);
    }
}
