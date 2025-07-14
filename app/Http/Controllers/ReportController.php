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
}
