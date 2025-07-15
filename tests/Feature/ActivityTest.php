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

class ActivityTest extends TestCase
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
            'codigo' => 'COORD-TEST-001',
            'descripcion' => 'Coordinación para tests'
        ]);

        $this->direccion = Direccion::create([
            'nombre' => 'Dirección de Prueba',
            'codigo' => 'DIR-TEST-001',
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
    public function empleado_puede_ver_index_de_actividades()
    {
        $this->actingAs($this->empleado);

        $response = $this->get(route('activities.index'));

        $response->assertStatus(200);
        $response->assertViewIs('activities.employee-index');
    }

    /** @test */
    public function jefe_puede_ver_index_de_actividades()
    {
        $this->actingAs($this->jefe);

        $response = $this->get(route('activities.index'));

        $response->assertStatus(200);
        $response->assertViewIs('activities.index');
    }

    /** @test */
    public function empleado_puede_crear_actividad()
    {
        $this->actingAs($this->empleado);

        $response = $this->get(route('activities.create'));
        $response->assertStatus(200);

        $activityData = [
            'titulo' => 'Actividad de prueba',
            'tipo' => ActivityType::QUIPUX->value,
            'numero_referencia' => 'QX-2024-001',
            'tiempo' => 2.5,
            'observaciones' => 'Observaciones de prueba'
        ];

        $response = $this->post(route('activities.store'), $activityData);

        $response->assertRedirect(route('activities.index'));
        $response->assertSessionHas('success', 'Actividad creada exitosamente.');
        
        $this->assertDatabaseHas('activities', [
            'titulo' => 'Actividad de prueba',
            'tipo' => ActivityType::QUIPUX->value,
            'user_id' => $this->empleado->id,
        ]);
        
        // Verificar que la fecha sea la de hoy
        $activity = Activity::where('titulo', 'Actividad de prueba')->first();
        $this->assertEquals(Carbon::today()->format('Y-m-d'), Carbon::parse($activity->fecha_actividad)->format('Y-m-d'));
    }

    /** @test */
    public function actividad_requiere_campos_obligatorios()
    {
        $this->actingAs($this->empleado);

        $response = $this->post(route('activities.store'), []);

        $response->assertSessionHasErrors(['titulo', 'tipo', 'tiempo']);
    }

    /** @test */
    public function tipo_debe_ser_valido()
    {
        $this->actingAs($this->empleado);

        $response = $this->post(route('activities.store'), [
            'titulo' => 'Test',
            'tipo' => 'TIPO_INVALIDO',
            'tiempo' => 1.0
        ]);

        $response->assertSessionHasErrors(['tipo']);
    }

    /** @test */
    public function tiempo_debe_ser_numerico_positivo()
    {
        $this->actingAs($this->empleado);

        // Tiempo negativo
        $response = $this->post(route('activities.store'), [
            'titulo' => 'Test',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => -1.0
        ]);
        $response->assertSessionHasErrors(['tiempo']);

        // Tiempo cero
        $response = $this->post(route('activities.store'), [
            'titulo' => 'Test',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 0
        ]);
        $response->assertSessionHasErrors(['tiempo']);

        // Tiempo muy grande
        $response = $this->post(route('activities.store'), [
            'titulo' => 'Test',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1000
        ]);
        $response->assertSessionHasErrors(['tiempo']);
    }

    /** @test */
    public function empleado_puede_ver_solo_sus_actividades()
    {
        $otroEmpleado = User::create([
            'name' => 'Otro Empleado',
            'email' => 'otro@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        $actividadPropia = Activity::create([
            'titulo' => 'Mi actividad',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $actividadAjena = Activity::create([
            'titulo' => 'Actividad ajena',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $otroEmpleado->id
        ]);

        $this->actingAs($this->empleado);

        // Puede ver su propia actividad
        $response = $this->get(route('activities.show', $actividadPropia));
        $response->assertStatus(200);

        // No puede ver actividad ajena
        $response = $this->get(route('activities.show', $actividadAjena));
        $response->assertStatus(403);
    }

    /** @test */
    public function jefe_puede_ver_actividades_de_empleados_bajo_supervision()
    {
        $actividadEmpleado = Activity::create([
            'titulo' => 'Actividad del empleado',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->jefe);

        $response = $this->get(route('activities.show', $actividadEmpleado));
        $response->assertStatus(200);
    }

    /** @test */
    public function empleado_puede_editar_solo_actividades_del_dia_actual()
    {
        // Actividad de hoy
        $actividadHoy = Activity::create([
            'titulo' => 'Actividad hoy',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        // Actividad de ayer
        $actividadAyer = Activity::create([
            'titulo' => 'Actividad ayer',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::yesterday(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->empleado);

        // Puede editar actividad de hoy
        $response = $this->get(route('activities.edit', $actividadHoy));
        $response->assertStatus(200);

        // No puede editar actividad de ayer
        $response = $this->get(route('activities.edit', $actividadAyer));
        $response->assertRedirect(route('activities.index'));
        $response->assertSessionHas('error', 'Solo puedes editar actividades del día actual.');
    }

    /** @test */
    public function empleado_puede_actualizar_actividad_del_dia_actual()
    {
        $actividad = Activity::create([
            'titulo' => 'Título original',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->empleado);

        $updateData = [
            'titulo' => 'Título actualizado',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 2.5,
            'observaciones' => 'Observaciones actualizadas'
        ];

        $response = $this->put(route('activities.update', $actividad), $updateData);

        $response->assertRedirect(route('activities.index'));
        $response->assertSessionHas('success', 'Actividad actualizada exitosamente.');

        $this->assertDatabaseHas('activities', [
            'id' => $actividad->id,
            'titulo' => 'Título actualizado',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 2.5
        ]);
    }

    /** @test */
    public function empleado_no_puede_actualizar_actividad_de_ayer()
    {
        $actividad = Activity::create([
            'titulo' => 'Actividad ayer',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::yesterday(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->empleado);

        $response = $this->put(route('activities.update', $actividad), [
            'titulo' => 'Intento de actualización',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 2.0
        ]);

        $response->assertRedirect(route('activities.index'));
        $response->assertSessionHas('error', 'Solo puedes editar actividades del día actual.');
    }

    /** @test */
    public function empleado_puede_eliminar_actividad_del_dia_actual()
    {
        $actividad = Activity::create([
            'titulo' => 'Actividad a eliminar',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->empleado);

        $response = $this->delete(route('activities.destroy', $actividad));

        $response->assertRedirect(route('activities.index'));
        $response->assertSessionHas('success', 'Actividad eliminada exitosamente.');
        
        $this->assertDatabaseMissing('activities', [
            'id' => $actividad->id
        ]);
    }

    /** @test */
    public function empleado_no_puede_eliminar_actividad_de_ayer()
    {
        $actividad = Activity::create([
            'titulo' => 'Actividad ayer',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::yesterday(),
            'user_id' => $this->empleado->id
        ]);

        $this->actingAs($this->empleado);

        $response = $this->delete(route('activities.destroy', $actividad));

        $response->assertRedirect(route('activities.index'));
        $response->assertSessionHas('error', 'Solo puedes eliminar actividades del día actual.');
        
        $this->assertDatabaseHas('activities', [
            'id' => $actividad->id
        ]);
    }

    /** @test */
    public function empleado_no_puede_editar_actividad_de_otro_usuario()
    {
        $otroEmpleado = User::create([
            'name' => 'Otro Empleado',
            'email' => 'otro@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $this->direccion->id,
            'coordinacion_id' => $this->coordinacion->id,
        ]);

        $actividadAjena = Activity::create([
            'titulo' => 'Actividad ajena',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $otroEmpleado->id
        ]);

        $this->actingAs($this->empleado);

        $response = $this->get(route('activities.edit', $actividadAjena));
        $response->assertStatus(403);

        $response = $this->put(route('activities.update', $actividadAjena), [
            'titulo' => 'Intento de modificación'
        ]);
        $response->assertStatus(403);

        $response = $this->delete(route('activities.destroy', $actividadAjena));
        $response->assertStatus(403);
    }

    /** @test */
    public function usuario_no_autenticado_no_puede_acceder_actividades()
    {
        $response = $this->get(route('activities.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('activities.create'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('activities.store'), []);
        $response->assertRedirect(route('login'));
    }
}
