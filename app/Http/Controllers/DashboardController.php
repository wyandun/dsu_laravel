<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isJefe()) {
            // Dashboard para jefe
            $stats = [
                'total_empleados' => User::where('role', 'empleado')->count(),
                'actividades_hoy' => Activity::today()->count(),
                'total_actividades' => Activity::count(),
                'tiempo_total_hoy' => Activity::today()->sum('tiempo'),
            ];
            
            $actividades_recientes = Activity::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
                
            return view('dashboard.jefe', compact('stats', 'actividades_recientes'));
        } else {
            // Dashboard para empleado
            $stats = [
                'actividades_hoy' => Activity::forUser($user->id)->today()->count(),
                'tiempo_hoy' => Activity::forUser($user->id)->today()->sum('tiempo'),
                'actividades_mes' => Activity::forUser($user->id)->whereMonth('fecha_actividad', Carbon::now()->month)->count(),
                'tiempo_mes' => Activity::forUser($user->id)->whereMonth('fecha_actividad', Carbon::now()->month)->sum('tiempo'),
            ];
            
            $actividades_recientes = Activity::forUser($user->id)
                ->orderBy('fecha_actividad', 'desc')
                ->limit(5)
                ->get();
                
            return view('dashboard.empleado', compact('stats', 'actividades_recientes'));
        }
    }
}
