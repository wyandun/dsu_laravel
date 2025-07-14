<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 * 
 * Representa los usuarios del sistema con estructura jerárquica organizacional normalizada.
 * 
 * ESTRUCTURA NORMALIZADA:
 * - Los usuarios tienen solo direccion_id (llave foránea)
 * - La coordinación se obtiene a través de: user.direccion.coordinacion
 * - Esto evita redundancia y mantiene integridad referencial
 * 
 * NOTA: Este modelo está preparado para integración con Active Directory.
 * Los campos direccion_id y tipo_jefe se mapearán desde atributos AD.
 * 
 * Estructura de roles:
 * - empleado: Usuario básico que gestiona sus propias actividades
 * - jefe: Usuario supervisor con dos subtipos:
 *   - director: Supervisa una dirección específica
 *   - coordinador: Supervisa todas las direcciones de una coordinación
 * - administrador: Acceso total al sistema
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tipo_jefe',
        'direccion_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con actividades
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Relación con coordinación a través de dirección
     */
    public function coordinacion()
    {
        return $this->hasOneThrough(Coordinacion::class, Direccion::class, 'id', 'id', 'direccion_id', 'coordinacion_id');
    }

    /**
     * Relación con dirección
     */
    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    /**
     * Verificar si el usuario es jefe
     */
    public function isJefe()
    {
        return $this->role === 'jefe';
    }

    /**
     * Verificar si el usuario es empleado
     */
    public function isEmpleado()
    {
        return $this->role === 'empleado';
    }

    /**
     * Verificar si el usuario es director
     */
    public function isDirector()
    {
        return $this->role === 'jefe' && $this->tipo_jefe === 'director';
    }

    /**
     * Verificar si el usuario es coordinador
     */
    public function isCoordinador()
    {
        return $this->role === 'jefe' && $this->tipo_jefe === 'coordinador';
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdministrador()
    {
        return $this->role === 'administrador';
    }

    /**
     * Obtener empleados que puede supervisar este jefe
     */
    public function getEmpleadosBajoSupervision()
    {
        // Solo jefes y administradores pueden supervisar
        if (!$this->isJefe() && !$this->isAdministrador()) {
            return collect();
        }

        $query = User::where('role', 'empleado');

        if ($this->isAdministrador()) {
            // El administrador puede ver todos los empleados
            return $query->get();
        } elseif ($this->isDirector()) {
            // El director puede ver solo empleados de su dirección específica
            $query->where('direccion_id', $this->direccion_id);
        } elseif ($this->isCoordinador()) {
            // El coordinador puede ver empleados de todas las direcciones de su coordinación
            // Como los coordinadores no tienen dirección específica, obtener todas las direcciones
            // de la coordinación TICS (por ahora solo hay una)
            $coordinacionTics = Coordinacion::where('codigo', 'TICS')->first();
            if ($coordinacionTics) {
                $direccionesIds = Direccion::where('coordinacion_id', $coordinacionTics->id)->pluck('id');
                $query->whereIn('direccion_id', $direccionesIds);
            }
        }

        return $query->get();
    }

    /**
     * Obtener todas las direcciones disponibles
     */
    public static function getDirecciones()
    {
        return Direccion::activas()->pluck('nombre')->toArray();
    }

    /**
     * Obtener todas las coordinaciones disponibles
     */
    public static function getCoordinaciones()
    {
        return Coordinacion::activas()->pluck('nombre')->toArray();
    }
}
