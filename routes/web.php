<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CollaborativeReportController;
use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas de actividades - disponibles para todos los usuarios autenticados
    Route::resource('activities', ActivityController::class);
    
    // Rutas de calendario - disponibles para todos los usuarios autenticados
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/day/{date}', [CalendarController::class, 'showDay'])->name('calendar.day');
    
    // APIs para autocompletado en calendario - solo para jefes y administradores
    Route::middleware('reports')->group(function () {
        Route::get('/api/calendar/empleados', [CalendarController::class, 'autocompleteEmpleados'])->name('calendar.empleados');
    });
    
    // Rutas de reportes - solo para jefes y administradores
    Route::middleware('reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        
        // Rutas de reportes colaborativos
        Route::get('/collaborative-reports', [CollaborativeReportController::class, 'index'])->name('collaborative-reports.index');
        Route::get('/collaborative-reports/export', [CollaborativeReportController::class, 'export'])->name('collaborative-reports.export');
        
        // APIs para autocompletado en reportes colaborativos
        Route::get('/api/autocomplete/referencia', [CollaborativeReportController::class, 'autocompleteReferencia'])->name('api.autocomplete.referencia');
        Route::get('/collaborative-reports/autocomplete/referencia', [CollaborativeReportController::class, 'autocompleteReferencia'])->name('collaborative-reports.autocomplete.referencia');
        Route::get('/api/autocomplete/titulos', [CollaborativeReportController::class, 'autocompleteTitulos'])->name('api.autocomplete.titulos');
        
        // APIs para autocompletado en reportes generales
        Route::get('/api/autocomplete/empleados', [ReportController::class, 'autocompleteEmpleados'])->name('api.autocomplete.empleados');
        Route::get('/api/autocomplete/direcciones', [ReportController::class, 'autocompleteDirecciones'])->name('api.autocomplete.direcciones');
        Route::get('/api/autocomplete/busqueda', [ReportController::class, 'autocompleteBusqueda'])->name('api.autocomplete.busqueda');
        
        // Rutas para gráficos de reportes colaborativos
        Route::get('/collaborative-reports/chart/hours-by-direction', [CollaborativeReportController::class, 'chartHoursByDirectionAndType'])->name('collaborative-reports.chart.hours-by-direction');
        Route::get('/collaborative-reports/chart/hours-by-employee', [CollaborativeReportController::class, 'chartHoursByEmployee'])->name('collaborative-reports.chart.hours-by-employee');
    });
});

require __DIR__.'/auth.php';
