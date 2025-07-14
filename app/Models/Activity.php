<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Enums\ActivityType;

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
        'fecha_actividad' => 'datetime',
        'tiempo' => 'decimal:2',
    ];

    /**
     * Boot method para aplicar validaciones automáticas
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            $activity->cleanWhitespace();
        });

        static::updating(function ($activity) {
            $activity->cleanWhitespace();
        });
    }

    /**
     * Limpiar espacios en blanco al inicio y final
     */
    public function cleanWhitespace()
    {
        $this->titulo = trim($this->titulo);
        $this->numero_referencia = trim($this->numero_referencia);
        $this->observaciones = trim($this->observaciones);
    }

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
        return $this->fecha_actividad->format('Y-m-d') === Carbon::today()->format('Y-m-d');
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

    /**
     * Scope para actividades por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('tipo', $type);
    }

    /**
     * Scope para actividades por número de referencia
     */
    public function scopeByReference($query, $reference)
    {
        return $query->where('numero_referencia', $reference);
    }

    /**
     * Scope para actividades colaborativas (mismo tipo y referencia)
     */
    public function scopeCollaborative($query, $type, $reference)
    {
        return $query->where('tipo', $type)
                    ->where('numero_referencia', $reference)
                    ->whereNotNull('numero_referencia')
                    ->where('numero_referencia', '!=', '');
    }

    /**
     * Obtener tipos de actividad disponibles
     */
    public static function getTipos()
    {
        return ActivityType::toArray();
    }
}
