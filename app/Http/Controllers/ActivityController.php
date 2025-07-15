<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Enums\ActivityType;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Obtener la semana actual o la semana seleccionada
        $weekStart = $request->get('week') 
            ? Carbon::parse($request->get('week'))->startOfWeek() 
            : Carbon::now()->startOfWeek();
        
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        if ($user->isJefe() || $user->isAdministrador()) {
            // Los jefes y administradores pueden ver actividades según su nivel de acceso
            $activities = collect();
            
            if ($user->isAdministrador()) {
                // El administrador puede ver todas las actividades
                $activities = Activity::with('user')
                    ->whereBetween('fecha_actividad', [$weekStart, $weekEnd])
                    ->orderBy('fecha_actividad', 'desc')
                    ->get();
            } elseif ($user->isJefe()) {
                // Los jefes pueden ver actividades de empleados bajo su supervisión
                $empleadosSupervision = $user->getEmpleadosBajoSupervision()->pluck('id');
                $activities = Activity::with('user')
                    ->whereIn('user_id', $empleadosSupervision)
                    ->whereBetween('fecha_actividad', [$weekStart, $weekEnd])
                    ->orderBy('fecha_actividad', 'desc')
                    ->get();
            }
            
            $activitiesGrouped = $activities->groupBy(function($activity) {
                return $activity->fecha_actividad->format('Y-m-d');
            });
            
            return view('activities.index', compact('activitiesGrouped', 'weekStart', 'weekEnd'));
        } else {
            // Los empleados ven sus actividades de la semana agrupadas por fecha
            $activities = Activity::forUser($user->id)
                ->whereBetween('fecha_actividad', [$weekStart, $weekEnd])
                ->orderBy('fecha_actividad', 'desc')
                ->get();
            
            $activitiesGrouped = $activities->groupBy(function($activity) {
                return $activity->fecha_actividad->format('Y-m-d');
            });
            
            return view('activities.employee-index', compact('activitiesGrouped', 'weekStart', 'weekEnd'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('activities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|in:' . implode(',', ActivityType::toArray()),
            'numero_referencia' => 'nullable|string|max:255',
            'tiempo' => 'required|numeric|min:0.01|max:999.99',
            'observaciones' => 'nullable|string',
        ]);

        // Usar automáticamente la fecha actual
        $validated['fecha_actividad'] = Carbon::today();
        $validated['user_id'] = Auth::id();

        Activity::create($validated);

        return redirect()->route('activities.index')->with('success', 'Actividad creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        $user = Auth::user();
        
        // Si es el propietario de la actividad, puede verla
        if ($activity->user_id === $user->id) {
            return view('activities.show', compact('activity'));
        }
        
        // Si es administrador, puede ver todas las actividades
        if ($user->isAdministrador()) {
            return view('activities.show', compact('activity'));
        }
        
        // Si es jefe, verificar que el empleado esté bajo su supervisión
        if ($user->isJefe()) {
            $empleadosSupervision = $user->getEmpleadosBajoSupervision()->pluck('id');
            if ($empleadosSupervision->contains($activity->user_id)) {
                return view('activities.show', compact('activity'));
            }
        }
        
        // Si no tiene permisos, denegar acceso
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        // Solo el propietario puede editar y solo si es del día actual (para empleados)
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }
        
        if (Auth::user()->isEmpleado() && !$activity->isToday()) {
            return redirect()->route('activities.index')->with('error', 'Solo puedes editar actividades del día actual.');
        }
        
        return view('activities.edit', compact('activity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        // Solo el propietario puede actualizar y solo si es del día actual (para empleados)
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }
        
        if (Auth::user()->isEmpleado() && !$activity->isToday()) {
            return redirect()->route('activities.index')->with('error', 'Solo puedes editar actividades del día actual.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|in:' . implode(',', ActivityType::toArray()),
            'numero_referencia' => 'nullable|string|max:255',
            'tiempo' => 'required|numeric|min:0.01|max:999.99',
            'observaciones' => 'nullable|string',
        ]);

        // Solo los jefes pueden modificar la fecha
        if (Auth::user()->isJefe()) {
            $request->validate([
                'fecha_actividad' => 'required|date',
            ]);
            $validated['fecha_actividad'] = $request->fecha_actividad;
        }

        $activity->update($validated);

        return redirect()->route('activities.index')->with('success', 'Actividad actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        // Solo el propietario puede eliminar y solo si es del día actual (para empleados)
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }
        
        if (Auth::user()->isEmpleado() && !$activity->isToday()) {
            return redirect()->route('activities.index')->with('error', 'Solo puedes eliminar actividades del día actual.');
        }

        $activity->delete();

        return redirect()->route('activities.index')->with('success', 'Actividad eliminada exitosamente.');
    }
}
