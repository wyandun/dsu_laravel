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

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_tiene_relacion_con_direccion()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación Test',
            'codigo' => 'COORD-TEST-M01',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M01',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
            'coordinacion_id' => $coordinacion->id,
        ]);

        $this->assertInstanceOf(Direccion::class, $user->direccion);
        $this->assertEquals('Dirección Test', $user->direccion->nombre);
    }

    /** @test */
    public function user_tiene_relacion_con_coordinacion()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación Test',
            'codigo' => 'COORD-TEST-M02',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M02',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
            'coordinacion_id' => $coordinacion->id,
        ]);

        $this->assertInstanceOf(Coordinacion::class, $user->coordinacion);
        $this->assertEquals('Coordinación Test', $user->coordinacion->nombre);
    }

    /** @test */
    public function direccion_tiene_relacion_con_coordinacion()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación Test',
            'codigo' => 'COORD-TEST-M03',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M03',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        $this->assertInstanceOf(Coordinacion::class, $direccion->coordinacion);
        $this->assertEquals('Coordinación Test', $direccion->coordinacion->nombre);
    }

    /** @test */
    public function user_tiene_relacion_con_activities()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación Test',
            'codigo' => 'COORD-TEST-M04',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M04',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
            'coordinacion_id' => $coordinacion->id,
        ]);

        Activity::create([
            'titulo' => 'Test Activity',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $user->id
        ]);

        $this->assertCount(1, $user->activities()->get());
        $this->assertInstanceOf(Activity::class, $user->activities()->first());
        $this->assertEquals('Test Activity', $user->activities()->first()->titulo);
    }

    /** @test */
    public function activity_tiene_relacion_con_user()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación Test',
            'codigo' => 'COORD-TEST-M05',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M05',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
            'coordinacion_id' => $coordinacion->id,
        ]);

        $activity = Activity::create([
            'titulo' => 'Test Activity',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $activity->user);
        $this->assertEquals('Test User', $activity->user->name);
    }

    /** @test */
    public function user_methods_funcionan_correctamente()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación Test',
            'codigo' => 'COORD-TEST-M06',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M06',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        // Empleado
        $empleado = User::create([
            'name' => 'Empleado',
            'email' => 'empleado@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
        ]);

        // Coordinador
        $coordinador = User::create([
            'name' => 'Coordinador',
            'email' => 'coordinador@test.com',
            'password' => bcrypt('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'coordinador',
            'direccion_id' => null, // Los coordinadores no tienen dirección específica
        ]);

        // Director
        $director = User::create([
            'name' => 'Director',
            'email' => 'director@test.com',
            'password' => bcrypt('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'director',
            'direccion_id' => $direccion->id,
        ]);

        // Administrador
        $administrador = User::create([
            'name' => 'Administrador',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'administrador',
            'direccion_id' => $direccion->id,
        ]);

        // Verificar métodos de roles
        $this->assertTrue($empleado->isEmpleado());
        $this->assertFalse($empleado->isJefe());
        $this->assertFalse($empleado->isAdministrador());

        $this->assertFalse($coordinador->isEmpleado());
        $this->assertTrue($coordinador->isJefe());
        $this->assertTrue($coordinador->isCoordinador());
        $this->assertFalse($coordinador->isAdministrador());

        $this->assertFalse($director->isEmpleado());
        $this->assertTrue($director->isJefe());
        $this->assertTrue($director->isDirector());
        $this->assertFalse($director->isAdministrador());

        $this->assertFalse($administrador->isEmpleado());
        $this->assertFalse($administrador->isJefe()); // Los administradores no son jefes
        $this->assertTrue($administrador->isAdministrador());
    }

    /** @test */
    public function activity_scopes_funcionan_correctamente()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación Test',
            'codigo' => 'COORD-TEST-M07',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M07',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
            'coordinacion_id' => $coordinacion->id,
        ]);

        // Crear actividades
        Activity::create([
            'titulo' => 'Actividad hoy',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $user->id
        ]);

        Activity::create([
            'titulo' => 'Actividad ayer',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 2.0,
            'fecha_actividad' => Carbon::yesterday(),
            'user_id' => $user->id
        ]);

        Activity::create([
            'titulo' => 'Actividad este mes',
            'tipo' => ActivityType::CORREO->value,
            'tiempo' => 1.5,
            'fecha_actividad' => Carbon::now()->startOfMonth()->addDays(10),
            'user_id' => $user->id
        ]);

        // Probar scope forUser
        $activitiesForUser = Activity::forUser($user->id)->get();
        $this->assertCount(3, $activitiesForUser);

        // Probar scope today
        $activitiesToday = Activity::today()->get();
        $this->assertCount(1, $activitiesToday);
        $this->assertEquals('Actividad hoy', $activitiesToday->first()->titulo);

        // Probar scope forUser + today
        $userActivitiesToday = Activity::forUser($user->id)->today()->get();
        $this->assertCount(1, $userActivitiesToday);
    }

    /** @test */
    public function activity_isToday_method_funciona()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación Test',
            'codigo' => 'COORD-TEST-M08',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M08',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
            'coordinacion_id' => $coordinacion->id,
        ]);

        $actividadHoy = Activity::create([
            'titulo' => 'Actividad hoy',
            'tipo' => ActivityType::QUIPUX->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::today(),
            'user_id' => $user->id
        ]);

        $actividadAyer = Activity::create([
            'titulo' => 'Actividad ayer',
            'tipo' => ActivityType::HELPDESK->value,
            'tiempo' => 1.0,
            'fecha_actividad' => Carbon::yesterday(),
            'user_id' => $user->id
        ]);

        $this->assertTrue($actividadHoy->isToday());
        $this->assertFalse($actividadAyer->isToday());
    }

    /** @test */
    public function get_empleados_bajo_supervision_funciona()
    {
        $coordinacion = Coordinacion::create([
            'nombre' => 'Coordinación de TICS',
            'codigo' => 'TICS',
            'descripcion' => 'Test'
        ]);

        $direccion = Direccion::create([
            'nombre' => 'Dirección Test',
            'codigo' => 'DIR-TEST-M09',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        $otraDireccion = Direccion::create([
            'nombre' => 'Otra Dirección',
            'codigo' => 'DIR-TEST-M09B',
            'descripcion' => 'Test',
            'coordinacion_id' => $coordinacion->id
        ]);

        // Coordinador (sin dirección específica, supervisa toda la coordinación)
        $coordinador = User::create([
            'name' => 'Coordinador',
            'email' => 'coordinador@test.com',
            'password' => bcrypt('password'),
            'role' => 'jefe',
            'tipo_jefe' => 'coordinador',
            'direccion_id' => null,
        ]);

        // Empleados en la misma dirección
        $empleado1 = User::create([
            'name' => 'Empleado 1',
            'email' => 'empleado1@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
        ]);

        $empleado2 = User::create([
            'name' => 'Empleado 2',
            'email' => 'empleado2@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $direccion->id,
        ]);

        // Empleado en otra dirección (de la misma coordinación)
        $empleadoOtraDireccion = User::create([
            'name' => 'Empleado Otra Dirección',
            'email' => 'empleado_otra@test.com',
            'password' => bcrypt('password'),
            'role' => 'empleado',
            'direccion_id' => $otraDireccion->id,
        ]);

        $empleadosBajoSupervision = $coordinador->getEmpleadosBajoSupervision();

        // El coordinador debería ver empleados de TODAS las direcciones de su coordinación
        $this->assertCount(3, $empleadosBajoSupervision);
        
        $ids = $empleadosBajoSupervision->pluck('id')->toArray();
        $this->assertContains($empleado1->id, $ids);
        $this->assertContains($empleado2->id, $ids);
        $this->assertContains($empleadoOtraDireccion->id, $ids); // Debe incluir empleados de TODAS las direcciones
    }
}
