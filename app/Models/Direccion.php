<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Direccion
 * 
 * Representa las direcciones organizacionales del sistema.
 * Cada dirección pertenece a una coordinación.
 */
class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'coordinacion_id',
        'nombre',
        'codigo',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /**
     * Relación con coordinación
     */
    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class);
    }

    /**
     * Relación con usuarios (empleados y director de la dirección)
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'direccion_id');
    }

    /**
     * Scope para direcciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Obtener el director de esta dirección
     */
    public function getDirector()
    {
        return $this->usuarios()
                   ->where('role', 'jefe')
                   ->where('tipo_jefe', 'director')
                   ->first();
    }

    /**
     * Obtener empleados de esta dirección
     */
    public function getEmpleados()
    {
        return $this->usuarios()
                   ->where('role', 'empleado')
                   ->get();
    }
}
