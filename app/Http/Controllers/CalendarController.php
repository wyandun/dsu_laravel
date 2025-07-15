<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Enums\ActivityType;

class CalendarController extends Controller
{
    /**
     * Display the calendar view
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Configurar Carbon en español
        Carbon::setLocale('es');
        
        // Obtener el mes y año actual o los seleccionados
        $monthNumber = $request->get('month', Carbon::now()->month);
        $yearNumber = $request->get('year', Carbon::now()->year);
        
        // Crear la fecha del primer día del mes
        $month = Carbon::createFromDate($yearNumber, $monthNumber, 1)->startOfMonth();
        
        $monthEnd = $month->copy()->endOfMonth();
        
        // Si es jefe o administrador, puede seleccionar empleado
        $selectedEmployee = null;
        $employees = collect();
        
        if ($user->isJefe() || $user->isAdministrador()) {
            // Obtener empleados bajo supervisión con sus direcciones
            $employees = $user->getEmpleadosBajoSupervision()->load('direccion');
            
            if ($request->has('employee_id') && $request->get('employee_id')) {
                $selectedEmployee = User::with('direccion')->find($request->get('employee_id'));
                // Verificar que el empleado esté bajo supervisión (excepto para administradores)
                if (!$user->isAdministrador() && !$employees->contains($selectedEmployee)) {
                    abort(403, 'No tienes acceso a las actividades de este empleado.');
                }
            } else {
                // Por defecto, mostrar las actividades del primer empleado bajo supervisión
                $selectedEmployee = $employees->first();
            }
        } else {
            // Los empleados solo ven sus propias actividades
            $selectedEmployee = $user;
            $employees = collect([$user]); // Para que aparezca en la lista
        }
        
        // Obtener actividades (siempre, incluso si no hay empleado seleccionado)
        $activities = collect();
        if ($selectedEmployee) {
            // Obtener actividades del empleado seleccionado para el mes
            $activities = Activity::where('user_id', $selectedEmployee->id)
                ->whereBetween('fecha_actividad', [$month, $monthEnd])
                ->orderBy('fecha_actividad', 'asc')
                ->get();
        }
        
        // Agrupar actividades por fecha y calcular horas diarias
        $activitiesByDate = $activities->groupBy(function($activity) {
            return $activity->fecha_actividad->format('Y-m-d');
        });
        
        $dailyHours = $activitiesByDate->map(function($dayActivities) {
            return $dayActivities->sum('tiempo');
        });
        
        return view('calendar.index', compact(
            'activities', 
            'monthEnd', 
            'selectedEmployee', 
            'employees',
            'activitiesByDate',
            'dailyHours',
            'month'
        ))->with([
            'year' => $yearNumber
        ]);
    }
    
    /**
     * Show activities for a specific day
     */
    public function showDay(Request $request, $date)
    {
        $user = Auth::user();
        $selectedDate = Carbon::parse($date);
        
        // Verificar empleado seleccionado
        $employeeId = $request->get('employee_id');
        $selectedEmployee = null;
        
        if (($user->isJefe() || $user->isAdministrador()) && $employeeId) {
            $selectedEmployee = User::find($employeeId);
            
            // Solo verificar supervisión para jefes (no para administradores)
            if ($user->isJefe() && !$user->isAdministrador()) {
                $employees = $user->getEmpleadosBajoSupervision();
                if (!$employees->contains($selectedEmployee)) {
                    abort(403, 'No tienes acceso a las actividades de este empleado.');
                }
            }
        } else {
            $selectedEmployee = $user;
        }
        
        // Obtener actividades del día
        $activities = Activity::where('user_id', $selectedEmployee->id)
            ->whereDate('fecha_actividad', $selectedDate)
            ->orderBy('created_at', 'asc')
            ->get();
        
        return view('calendar.day', compact('activities', 'selectedDate', 'selectedEmployee'));
    }
    
    /**
     * Autocompletado para empleados en el calendario
     */
    public function autocompleteEmpleados(Request $request)
    {
        $user = Auth::user();
        
        // Solo los jefes y administradores pueden usar este endpoint
        if (!$user->isJefe() && !$user->isAdministrador()) {
            abort(403, 'No tienes permisos para acceder a este recurso.');
        }
        
        $term = $request->get('q', '');
        
        if ($user->isAdministrador()) {
            // Los administradores pueden ver todos los empleados
            $empleados = User::where('role', 'empleado')
                ->where('name', 'like', "%{$term}%")
                ->limit(10)
                ->get(['id', 'name', 'email']);
        } else {
            // Los jefes solo ven empleados bajo su supervisión
            $empleados = $user->getEmpleadosBajoSupervision()
                ->where('name', 'like', "%{$term}%")
                ->take(10);
        }
        
        return response()->json($empleados->map(function($empleado) {
            return [
                'id' => $empleado->id,
                'text' => $empleado->name . ' (' . $empleado->email . ')'
            ];
        }));
    }
}
