<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
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
        
        // Solo los jefes pueden acceder a los reportes
        if (!$user->isJefe()) {
            abort(403, 'No tienes permisos para acceder a los reportes.');
        }

        $query = Activity::with('user');
        
        // Filtrar según el tipo de jefe
        if ($user->isDirector()) {
            // El director solo ve empleados de su dirección
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        } elseif ($user->isCoordinador()) {
            // El coordinador ve empleados de todas las direcciones de su coordinación
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }
        
        // Aplicar filtros adicionales
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('direccion')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('direccion', $request->direccion);
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

        // Obtener datos para los filtros basados en la supervisión del jefe
        $users = $user->getEmpleadosBajoSupervision()->sortBy('name');
        
        $direcciones = $users->pluck('direccion')
                            ->unique()
                            ->filter()
                            ->sort()
                            ->values();
                            
        $tipos = ['Quipux', 'Mantis', 'CTIT', 'Correo', 'Otros'];

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
        
        if (!$user->isJefe()) {
            abort(403, 'No tienes permisos para exportar reportes.');
        }

        $query = Activity::with('user');
        
        // Filtrar según el tipo de jefe
        if ($user->isDirector()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        } elseif ($user->isCoordinador()) {
            $empleadosIds = $user->getEmpleadosBajoSupervision()->pluck('id');
            $query->whereIn('user_id', $empleadosIds);
        }
        
        // Aplicar los mismos filtros que en index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('direccion')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('direccion', $request->direccion);
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
}
