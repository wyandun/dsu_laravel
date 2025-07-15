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
        
        // Obtener el mes actual o el mes seleccionado
        $month = $request->get('month') 
            ? Carbon::parse($request->get('month'))->startOfMonth() 
            : Carbon::now()->startOfMonth();
        
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
        
        return view('calendar.index', compact(
            'activities', 
            'month', 
            'monthEnd', 
            'selectedEmployee', 
            'employees'
        ));
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
}
