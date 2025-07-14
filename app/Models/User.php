<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 * 
 * Representa los usuarios del sistema con estructura jerárquica organizacional.
 * 
 * NOTA: Este modelo está preparado para integración con Active Directory.
 * Los campos coordinacion, direccion y tipo_jefe se mapearán desde atributos AD.
 * 
 * Estructura de roles:
 * - empleado: Usuario básico que gestiona sus propias actividades
 * - jefe: Usuario supervisor con dos subtipos:
 *   - director: Supervisa una dirección específica
 *   - coordinador: Supervisa todas las direcciones de una coordinación
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
        'direccion',
        'coordinacion',
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
     * Obtener empleados que puede supervisar este jefe
     */
    public function getEmpleadosBajoSupervision()
    {
        if (!$this->isJefe()) {
            return collect();
        }

        $query = User::where('role', 'empleado');

        if ($this->isDirector()) {
            // El director puede ver solo empleados de su dirección específica
            $query->where('direccion', $this->direccion)
                  ->where('coordinacion', $this->coordinacion);
        } elseif ($this->isCoordinador()) {
            // El coordinador puede ver empleados de todas las direcciones de su coordinación
            $query->where('coordinacion', $this->coordinacion);
        }

        return $query->get();
    }

    /**
     * Obtener todas las direcciones disponibles
     */
    public static function getDirecciones()
    {
        return [
            'Dirección de Seguridad',
            'Dirección de Infraestructura',
            'Dirección de Desarrollo de Soluciones',
            'Dirección de Gestión de Servicios Informáticos'
        ];
    }

    /**
     * Obtener todas las coordinaciones disponibles
     */
    public static function getCoordinaciones()
    {
        return [
            'Coordinación de TICS'
        ];
    }
}
