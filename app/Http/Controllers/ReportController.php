<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Enums\ActivityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActivitiesExport;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Solo los jefes y administradores pueden acceder a los reportes
        if (!$user->isJefe() && !$user->isAdministrador()) {
            abort(403, 'No tienes permisos para acceder a los reportes.');
        }

        $query = Activity::with(['user', 'user.direccion', 'user.coordinacion']);
        
        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }
        
        // Aplicar filtros adicionales
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('direccion')) {
            $query->whereHas('user.direccion', function($q) use ($request) {
                $q->where('nombre', $request->direccion);
            });
        }
        
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_actividad', '>=', $request->fecha_inicio);
        }
        
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_actividad', '<=', $request->fecha_fin);
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('numero_referencia', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $activities = $query->orderBy('fecha_actividad', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(15);

        // Obtener datos para los filtros basados en la supervisión del jefe o todos si es admin
        if ($user->isAdministrador()) {
            $users = User::where('role', 'empleado')->orderBy('name')->get();
            $direcciones = User::getDirecciones();
        } else {
            $users = $user->getEmpleadosBajoSupervision()->sortBy('name');
            $direcciones = $users->filter(function($user) {
                    return $user->direccion;
                })
                ->map(function($user) {
                    return $user->direccion->nombre;
                })
                ->unique()
                ->sort()
                ->values();
        }
                            
        $tipos = ActivityType::toArray();

        // Estadísticas
        $totalActividades = $query->count();
        $totalTiempo = $query->sum('tiempo');
        
        return view('reports.index', compact(
            'activities', 
            'users', 
            'direcciones', 
            'tipos',
            'totalActividades',
            'totalTiempo'
        ));
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            abort(403, 'No tienes permisos para exportar reportes.');
        }

        $query = Activity::with(['user', 'user.direccion', 'user.coordinacion']);
        
        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }
        
        // Aplicar los mismos filtros que en index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('direccion')) {
            $query->whereHas('user.direccion', function($q) use ($request) {
                $q->where('nombre', $request->direccion);
            });
        }
        
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_actividad', '>=', $request->fecha_inicio);
        }
        
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_actividad', '<=', $request->fecha_fin);
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('numero_referencia', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $activities = $query->orderBy('fecha_actividad', 'desc')->get();
        
        $filename = 'reporte_actividades_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new ActivitiesExport($activities), $filename);
    }

    /**
     * API para autocompletado de empleados
     */
    public function autocompleteEmpleados(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            return response()->json([]);
        }

        // Obtener empleados bajo supervisión o todos si es admin
        if ($user->isAdministrador()) {
            $query = User::where('role', 'empleado');
        } else {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query = User::whereIn('id', $empleadosIds);
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $empleados = $query->orderBy('name')
            ->limit(10)
            ->get(['id', 'name'])
            ->map(function($empleado) {
                return [
                    'id' => $empleado->id,
                    'text' => $empleado->name
                ];
            });

        return response()->json($empleados);
    }

    /**
     * API para autocompletado de direcciones
     */
    public function autocompleteDirecciones(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            return response()->json([]);
        }

        // Obtener direcciones bajo supervisión o todas si es admin
        if ($user->isAdministrador()) {
            $direcciones = User::whereHas('direccion')
                ->with('direccion')
                ->get()
                ->map(function($user) {
                    return $user->direccion->nombre;
                })
                ->unique()
                ->sort()
                ->values();
        } else {
            $empleados = $user->getEmpleadosBajoSupervision();
            $direcciones = $empleados->filter(function($empleado) {
                    return $empleado->direccion;
                })
                ->map(function($empleado) {
                    return $empleado->direccion->nombre;
                })
                ->unique()
                ->sort()
                ->values();
        }

        if ($request->filled('q')) {
            $direcciones = $direcciones->filter(function($direccion) use ($request) {
                return stripos($direccion, $request->q) !== false;
            })->values();
        }

        return response()->json($direcciones->take(10));
    }

    /**
     * API para autocompletado de búsqueda general
     */
    public function autocompleteBusqueda(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isJefe() && !$user->isAdministrador()) {
            return response()->json([]);
        }

        $query = Activity::with(['user']);

        // Aplicar filtros jerárquicos solo si NO es administrador
        if (!$user->isAdministrador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('numero_referencia', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%");
            });
        }

        $resultados = $query->select('titulo', 'numero_referencia')
            ->distinct()
            ->limit(10)
            ->get()
            ->map(function($activity) {
                return $activity->titulo;
            })
            ->unique()
            ->values();

        return response()->json($resultados);
    }
}
