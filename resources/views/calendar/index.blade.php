<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Calendario de Actividades') }}
                @if($selectedEmployee)
                    - {{ $selectedEmployee->name }}
                @endif
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('activities.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Vista Lista
                </a>                        @if(Auth::user()->isJefe() || Auth::user()->isAdministrador())
                            <a href="{{ route('reports.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Reportes
                            </a>
                        @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Controles del calendario -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <!-- Navegación de mes y selector de empleado -->
                    <div class="flex justify-between items-center mb-4">
                        @php
                            $prevMonth = $month->copy()->subMonth();
                            $nextMonth = $month->copy()->addMonth();
                            $currentParams = request()->only(['employee_id']);
                        @endphp
                        
                        <a href="{{ route('calendar.index', array_merge($currentParams, ['month' => $prevMonth->format('Y-m-d')])) }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ← {{ $prevMonth->format('F') }}
                        </a>
                        
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-gray-900">
                                {{ $month->format('F Y') }}
                            </h3>
                        </div>
                        
                        <a href="{{ route('calendar.index', array_merge($currentParams, ['month' => $nextMonth->format('Y-m-d')])) }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            {{ $nextMonth->format('F') }} →
                        </a>
                    </div>
                    
                    <!-- Selector de empleado (solo para jefes y administradores) -->
                    @if((Auth::user()->isJefe() || Auth::user()->isAdministrador()) && $employees->count() > 0)
                        <div class="flex justify-center">
                            <form method="GET" action="{{ route('calendar.index') }}" class="flex items-center space-x-4">
                                <input type="hidden" name="month" value="{{ $month->format('Y-m-d') }}">
                                <label for="employee_id" class="text-sm font-medium text-gray-700">
                                    Empleado:
                                </label>
                                <select name="employee_id" id="employee_id" 
                                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        onchange="this.form.submit()">
                                    <option value="">Seleccionar empleado...</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" 
                                                {{ $selectedEmployee && $selectedEmployee->id == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} - {{ $employee->direccion->nombre ?? 'Sin dirección' }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @endif
                    
                    <!-- Botón para ir al mes actual -->
                    @if(!$month->isSameMonth(Carbon\Carbon::now()))
                        <div class="text-center mt-4">
                            <a href="{{ route('calendar.index', $currentParams) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Ir a Mes Actual
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @if($selectedEmployee)
                <!-- Estadísticas del mes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            @php
                                $totalActivities = $activities->count();
                                $totalHours = $activities->sum('tiempo');
                                $activeDays = $activities->groupBy(function($activity) {
                                    return $activity->fecha_actividad->format('Y-m-d');
                                })->count();
                                $avgHoursPerDay = $activeDays > 0 ? $totalHours / $activeDays : 0;
                            @endphp
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">{{ $totalActivities }}</div>
                                <div class="text-gray-600">Total Actividades</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">{{ number_format($totalHours, 1) }}</div>
                                <div class="text-gray-600">Total Horas</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600">{{ $activeDays }}</div>
                                <div class="text-gray-600">Días Activos</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-orange-600">{{ number_format($avgHoursPerDay, 1) }}</div>
                                <div class="text-gray-600">Promedio Horas/Día</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendario -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Encabezados de días de la semana -->
                        <div class="grid grid-cols-7 gap-1 mb-2">
                            @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dayName)
                                <div class="text-center text-sm font-medium text-gray-500 py-2">
                                    {{ $dayName }}
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Días del calendario -->
                        @foreach($calendarData as $week)
                            <div class="grid grid-cols-7 gap-1 mb-1">
                                @foreach($week as $day)
                                    <div class="min-h-[120px] border rounded-lg p-2 
                                        {{ $day['is_current_month'] ? 'bg-white' : 'bg-gray-50' }}
                                        {{ $day['is_today'] ? 'ring-2 ring-blue-500' : '' }}">
                                        
                                        <!-- Número del día -->
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="text-sm font-medium 
                                                {{ $day['is_current_month'] ? 'text-gray-900' : 'text-gray-400' }}
                                                {{ $day['is_today'] ? 'text-blue-600 font-bold' : '' }}">
                                                {{ $day['date']->format('j') }}
                                            </span>
                                            @if($day['activity_count'] > 0)
                                                <span class="text-xs bg-blue-100 text-blue-800 px-1 py-0.5 rounded">
                                                    {{ $day['activity_count'] }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Actividades del día -->
                                        @if($day['activity_count'] > 0)
                                            <div class="space-y-1">
                                                @foreach($day['activities']->take(3) as $activity)
                                                    <div class="text-xs p-1 rounded cursor-pointer
                                                        @switch($activity->tipo)
                                                            @case('Quipux') bg-blue-100 text-blue-800 @break
                                                            @case('Mantis') bg-red-100 text-red-800 @break
                                                            @case('CTIT') bg-green-100 text-green-800 @break
                                                            @case('Correo') bg-yellow-100 text-yellow-800 @break
                                                            @default bg-gray-100 text-gray-800
                                                        @endswitch"
                                                        title="{{ $activity->titulo }} - {{ number_format($activity->tiempo, 1) }}h">
                                                        <div class="truncate">{{ $activity->titulo }}</div>
                                                        <div class="text-xs opacity-75">{{ number_format($activity->tiempo, 1) }}h</div>
                                                    </div>
                                                @endforeach
                                                
                                                @if($day['activity_count'] > 3)
                                                    <div class="text-xs text-gray-500 text-center">
                                                        +{{ $day['activity_count'] - 3 }} más
                                                    </div>
                                                @endif
                                                
                                                <!-- Total de horas del día -->
                                                <div class="text-xs font-medium text-gray-600 text-center border-t pt-1">
                                                    Total: {{ number_format($day['total_hours'], 1) }}h
                                                </div>
                                                
                                                <!-- Enlace para ver día completo -->
                                                <div class="text-center">
                                                    <a href="{{ route('calendar.day', [
                                                        'date' => $day['date']->format('Y-m-d'),
                                                        'employee_id' => $selectedEmployee->id
                                                    ]) }}" 
                                                       class="text-xs text-blue-600 hover:text-blue-800">
                                                        Ver detalle
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- No hay empleado seleccionado -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="text-gray-500 text-lg mb-4">
                            Selecciona un empleado para ver su calendario de actividades.
                        </div>
                        @if(Auth::user()->isJefe() || Auth::user()->isAdministrador())
                            <p class="text-gray-400">
                                Utiliza el selector de empleado para ver las actividades en formato calendario.
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
