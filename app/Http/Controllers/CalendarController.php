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
        
        // Obtener el mes actual o el mes seleccionado
        $month = $request->get('month') 
            ? Carbon::parse($request->get('month'))->startOfMonth() 
            : Carbon::now()->startOfMonth();
        
        $monthEnd = $month->copy()->endOfMonth();
        
        // Si es jefe o administrador, puede seleccionar empleado
        $selectedEmployee = null;
        $employees = collect();
        
        if ($user->isJefe() || $user->isAdministrador()) {
            // Obtener empleados bajo supervisión
            $employees = $user->getEmpleadosBajoSupervision();
            
            if ($request->has('employee_id') && $request->get('employee_id')) {
                $selectedEmployee = User::find($request->get('employee_id'));
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
        }
        
        if (!$selectedEmployee) {
            return view('calendar.index', [
                'activities' => collect(),
                'month' => $month,
                'monthEnd' => $monthEnd,
                'selectedEmployee' => null,
                'employees' => $employees,
                'calendarData' => []
            ]);
        }
        
        // Obtener actividades del empleado seleccionado para el mes
        $activities = Activity::where('user_id', $selectedEmployee->id)
            ->whereBetween('fecha_actividad', [$month, $monthEnd])
            ->orderBy('fecha_actividad', 'asc')
            ->get();
        
        // Organizar actividades por día para el calendario
        $calendarData = $this->buildCalendarData($month, $activities);
        
        return view('calendar.index', compact(
            'activities', 
            'month', 
            'monthEnd', 
            'selectedEmployee', 
            'employees', 
            'calendarData'
        ));
    }
    
    /**
     * Build calendar data structure
     */
    private function buildCalendarData($month, $activities)
    {
        $calendarData = [];
        $activitiesByDate = $activities->groupBy(function($activity) {
            return $activity->fecha_actividad->format('Y-m-d');
        });
        
        // Obtener el primer día de la semana del mes
        $startDate = $month->copy()->startOfWeek();
        // Obtener el último día de la semana del mes
        $endDate = $month->copy()->endOfMonth()->endOfWeek();
        
        // Construir las semanas
        $currentDate = $startDate->copy();
        $weekNumber = 0;
        
        while ($currentDate <= $endDate) {
            $weekData = [];
            
            // Construir los días de la semana
            for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++) {
                $dateString = $currentDate->format('Y-m-d');
                $dayActivities = $activitiesByDate->get($dateString, collect());
                
                $weekData[] = [
                    'date' => $currentDate->copy(),
                    'activities' => $dayActivities,
                    'is_current_month' => $currentDate->month === $month->month,
                    'is_today' => $currentDate->isToday(),
                    'total_hours' => $dayActivities->sum('tiempo'),
                    'activity_count' => $dayActivities->count()
                ];
                
                $currentDate->addDay();
            }
            
            $calendarData[] = $weekData;
            $weekNumber++;
        }
        
        return $calendarData;
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
