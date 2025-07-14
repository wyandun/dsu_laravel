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
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isJefe()) {
            // Los jefes pueden ver todas las actividades
            $activities = Activity::with('user')->orderBy('fecha_actividad', 'desc')->paginate(15);
            return view('activities.index', compact('activities'));
        } else {
            // Los empleados ven sus actividades agrupadas por fecha
            $activities = Activity::forUser($user->id)
                ->orderBy('fecha_actividad', 'desc')
                ->get();
            
            $activitiesGrouped = $activities->groupBy(function($activity) {
                return $activity->fecha_actividad->format('Y-m-d');
            });
            
            return view('activities.employee-index', compact('activitiesGrouped'));
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
        // Verificar permisos
        if (!Auth::user()->isJefe() && $activity->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('activities.show', compact('activity'));
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
