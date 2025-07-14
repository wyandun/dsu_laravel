<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Enums\ActivityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CollaborativeActivitiesExport;
use Carbon\Carbon;

class CollaborativeReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Solo los jefes y administradores pueden acceder a los reportes
        if (!$user->isJefe() && !$user->isAdministrador()) {
            abort(403, 'No tienes permisos para acceder a los reportes colaborativos.');
        }

        // Query base para actividades colaborativas
        $query = Activity::with('user')
            ->whereNotNull('numero_referencia')
            ->where('numero_referencia', '!=', '')
            ->where('numero_referencia', '!=', 'N/A');

        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }
        
        // Aplicar filtros adicionales
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('numero_referencia')) {
            $query->where('numero_referencia', 'like', '%' . $request->numero_referencia . '%');
        }
        
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_actividad', '>=', $request->fecha_inicio);
        }
        
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_actividad', '<=', $request->fecha_fin);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Agrupar por tipo y número de referencia
        $collaborativeGroups = $query->select(
                'tipo',
                'numero_referencia',
                DB::raw('COUNT(*) as total_actividades'),
                DB::raw('COUNT(DISTINCT user_id) as total_participantes'),
                DB::raw('SUM(tiempo) as total_tiempo'),
                DB::raw('MIN(fecha_actividad) as fecha_inicio'),
                DB::raw('MAX(fecha_actividad) as fecha_fin')
            )
            ->groupBy('tipo', 'numero_referencia')
            ->orderBy('tipo')
            ->orderBy('numero_referencia')
            ->paginate(10); // Reducir a 10 grupos por página

        // Para cada grupo, obtener los participantes
        foreach ($collaborativeGroups as $group) {
            $participantesQuery = Activity::with('user')
                ->where('tipo', $group->tipo)
                ->where('numero_referencia', $group->numero_referencia);
                
            // Aplicar filtros jerárquicos solo si NO es administrador
            if (!$user->isAdministrador()) {
                $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
                $participantesQuery->whereIn('user_id', $empleadosIds);
            }
            
            $group->participantes = $participantesQuery
                ->select('user_id', DB::raw('COUNT(*) as actividades_count'), DB::raw('SUM(tiempo) as tiempo_total'))
                ->groupBy('user_id')
                ->get();
        }

        // Obtener datos para los filtros
        $tipos = ActivityType::toArray();

        // Estadísticas generales
        $totalGrupos = $collaborativeGroups->total();
        $totalActividades = $query->count();
        $totalTiempo = $query->sum('tiempo');

        return view('collaborative-reports.index', compact(
            'collaborativeGroups',
            'tipos',
            'totalGrupos',
            'totalActividades',
            'totalTiempo'
        ));
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            abort(403, 'No tienes permisos para exportar reportes colaborativos.');
        }

        // Aplicar los mismos filtros que en index pero sin paginación
        $query = Activity::with('user')
            ->whereNotNull('numero_referencia')
            ->where('numero_referencia', '!=', '')
            ->where('numero_referencia', '!=', 'N/A');

        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }
        
        // Aplicar filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('numero_referencia')) {
            $query->where('numero_referencia', 'like', '%' . $request->numero_referencia . '%');
        }
        
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_actividad', '>=', $request->fecha_inicio);
        }
        
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_actividad', '<=', $request->fecha_fin);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $activities = $query->orderBy('tipo')
                           ->orderBy('numero_referencia')
                           ->orderBy('fecha_actividad')
                           ->get();
        
        $filename = 'reporte_colaborativo_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new CollaborativeActivitiesExport($activities), $filename);
    }
}
