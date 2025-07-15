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
        $query = Activity::with(['user.direccion.coordinacion'])
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
            $participantesQuery = Activity::with(['user.direccion.coordinacion'])
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

    /**
     * API para autocompletado de números de referencia
     */
    public function autocompleteReferencia(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            return response()->json([]);
        }

        $query = Activity::whereNotNull('numero_referencia')
            ->where('numero_referencia', '!=', '')
            ->where('numero_referencia', '!=', 'N/A');

        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }

        if ($request->filled('q')) {
            $query->where('numero_referencia', 'like', '%' . $request->q . '%');
        }

        $referencias = $query->select('numero_referencia')
            ->distinct()
            ->orderBy('numero_referencia')
            ->limit(10)
            ->pluck('numero_referencia');

        return response()->json($referencias);
    }

    /**
     * API para autocompletado de títulos
     */
    public function autocompleteTitulos(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            return response()->json([]);
        }

        $query = Activity::whereNotNull('numero_referencia')
            ->where('numero_referencia', '!=', '')
            ->where('numero_referencia', '!=', 'N/A');

        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }

        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->q . '%')
                  ->orWhere('observaciones', 'like', '%' . $request->q . '%');
            });
        }

        $titulos = $query->select('titulo')
            ->distinct()
            ->orderBy('titulo')
            ->limit(10)
            ->pluck('titulo');

        return response()->json($titulos);
    }

    /**
     * Obtener datos para gráfico de horas por dirección por tipo de actividad
     */
    public function chartHoursByDirectionAndType(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $tipo = $request->get('tipo', 'Quipux'); // Por defecto Quipux

        // Query base
        $query = Activity::with(['user.direccion'])
            ->where('tipo', $tipo)
            ->whereNotNull('numero_referencia')
            ->where('numero_referencia', '!=', '')
            ->where('numero_referencia', '!=', 'N/A');

        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }

        // Filtros de fecha si se proporcionan
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_actividad', '>=', $request->fecha_inicio);
        }
        
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_actividad', '<=', $request->fecha_fin);
        }

        // Agrupar por dirección y sumar horas
        $data = $query->get()
            ->groupBy(function($activity) {
                return $activity->user->direccion->nombre ?? 'Sin Dirección';
            })
            ->map(function($activities) {
                return $activities->sum('tiempo');
            })
            ->filter(function($hours) {
                return $hours > 0;
            });

        // Preparar datos para Chart.js
        $labels = $data->keys()->toArray();
        $values = $data->values()->toArray();
        
        // Colores para el gráfico
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
        ];

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'borderWidth' => 2,
                    'borderColor' => '#fff'
                ]
            ],
            'title' => "Horas por Dirección - Tipo: {$tipo}",
            'total' => array_sum($values)
        ]);
    }

    /**
     * Obtener datos para gráfico de horas por empleado (recurso)
     */
    public function chartHoursByEmployee(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Query base
        $query = Activity::with(['user'])
            ->whereNotNull('numero_referencia')
            ->where('numero_referencia', '!=', '')
            ->where('numero_referencia', '!=', 'N/A');

        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }

        // Filtros adicionales
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_actividad', '>=', $request->fecha_inicio);
        }
        
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_actividad', '<=', $request->fecha_fin);
        }

        // Agrupar por empleado y sumar horas
        $data = $query->get()
            ->groupBy(function($activity) {
                return $activity->user->name;
            })
            ->map(function($activities) {
                return $activities->sum('tiempo');
            })
            ->filter(function($hours) {
                return $hours > 0;
            })
            ->sortDesc()
            ->take(10); // Solo los 10 empleados con más horas

        // Preparar datos para Chart.js
        $labels = $data->keys()->toArray();
        $values = $data->values()->toArray();
        
        // Colores para el gráfico
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
        ];

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'borderWidth' => 2,
                    'borderColor' => '#fff'
                ]
            ],
            'title' => 'Top 10 Empleados por Horas Trabajadas',
            'total' => array_sum($values)
        ]);
    }
}
