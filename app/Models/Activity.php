<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titulo',
        'tipo',
        'numero_referencia',
        'tiempo',
        'observaciones',
        'fecha_actividad',
    ];

    protected $casts = [
        'fecha_actividad' => 'date',
        'tiempo' => 'decimal:2',
    ];

    /**
     * Relación con usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si la actividad es del día actual
     */
    public function isToday()
    {
        return $this->fecha_actividad === Carbon::today()->toDateString();
    }

    /**
     * Scope para actividades del día actual
     */
    public function scopeToday($query)
    {
        return $query->whereDate('fecha_actividad', Carbon::today());
    }

    /**
     * Scope para actividades de un usuario específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
