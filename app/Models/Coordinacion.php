<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Coordinacion
 * 
 * Representa las coordinaciones organizacionales del sistema.
 * Cada coordinación puede tener múltiples direcciones.
 */
class Coordinacion extends Model
{
    use HasFactory;

    protected $table = 'coordinaciones';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /**
     * Relación con direcciones
     */
    public function direcciones()
    {
        return $this->hasMany(Direccion::class);
    }

    /**
     * Relación con usuarios a través de direcciones
     */
    public function usuarios()
    {
        return $this->hasManyThrough(User::class, Direccion::class, 'coordinacion_id', 'direccion_id');
    }

    /**
     * Scope para coordinaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Obtener todas las direcciones activas de esta coordinación
     */
    public function getDireccionesActivas()
    {
        return $this->direcciones()->where('activa', true)->get();
    }
}
