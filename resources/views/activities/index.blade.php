<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Actividades del Equipo') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('activities.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Nueva Actividad
                </a>
                <a href="{{ route('calendar.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Vista Calendario
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Navegación de semanas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        @php
                            $prevWeek = $weekStart->copy()->subWeek();
                            $nextWeek = $weekStart->copy()->addWeek();
                        @endphp
                        
                        <a href="{{ route('activities.index', ['week' => $prevWeek->format('Y-m-d')]) }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ← Semana Anterior
                        </a>
                        
                        <div class="text-center">
                            <h3 class="text-lg font-semibold">
                                Semana del {{ $weekStart->format('d/m/Y') }} al {{ $weekEnd->format('d/m/Y') }}
                            </h3>
                            <p class="text-gray-600">
                                {{ $weekStart->format('F Y') }}
                            </p>
                        </div>
                        
                        <a href="{{ route('activities.index', ['week' => $nextWeek->format('Y-m-d')]) }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Semana Siguiente →
                        </a>
                    </div>
                    
                    <!-- Botón para volver a la semana actual -->
                    @if(!$weekStart->isSameWeek(Carbon\Carbon::now()))
                        <div class="text-center mt-4">
                            <a href="{{ route('activities.index') }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Ir a Semana Actual
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @if($activitiesGrouped->count() > 0)
                <!-- Estadísticas de la semana -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            @php
                                $allActivities = $activitiesGrouped->flatten();
                                $totalActivities = $allActivities->count();
                                $totalHours = $allActivities->sum('tiempo');
                                $totalDays = $activitiesGrouped->count();
                                $uniqueUsers = $allActivities->unique('user_id')->count();
                            @endphp
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">{{ $totalActivities }}</div>
                                <div class="text-gray-600">Total de Actividades</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">{{ number_format($totalHours, 2) }}</div>
                                <div class="text-gray-600">Total de Horas</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600">{{ $totalDays }}</div>
                                <div class="text-gray-600">Días con Actividades</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-orange-600">{{ $uniqueUsers }}</div>
                                <div class="text-gray-600">Empleados Activos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividades agrupadas por día -->
                @foreach($activitiesGrouped as $date => $dailyActivities)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ Carbon\Carbon::parse($date)->format('l, d/m/Y') }}
                                </h3>
                                <div class="text-sm text-gray-600">
                                    {{ $dailyActivities->count() }} actividades - 
                                    {{ number_format($dailyActivities->sum('tiempo'), 2) }} horas
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Empleado
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Título
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tipo
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Referencia
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tiempo
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($dailyActivities as $activity)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $activity->user->name }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    {{ $activity->titulo }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @switch($activity->tipo)
                                                            @case('Quipux') bg-blue-100 text-blue-800 @break
                                                            @case('Mantis') bg-red-100 text-red-800 @break
                                                            @case('CTIT') bg-green-100 text-green-800 @break
                                                            @case('Correo') bg-yellow-100 text-yellow-800 @break
                                                            @default bg-gray-100 text-gray-800
                                                        @endswitch">
                                                        {{ $activity->tipo }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    {{ $activity->numero_referencia ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($activity->tiempo, 2) }}h
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <a href="{{ route('activities.show', $activity) }}" 
                                                           class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                                        @if($activity->user_id === Auth::id())
                                                            <a href="{{ route('activities.edit', $activity) }}" 
                                                               class="text-blue-600 hover:text-blue-900">Editar</a>
                                                            <form action="{{ route('activities.destroy', $activity) }}" 
                                                                  method="POST" 
                                                                  class="inline"
                                                                  onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta actividad?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" 
                                                                        class="text-red-600 hover:text-red-900">Eliminar</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <div class="text-gray-500 text-lg mb-4">
                            No hay actividades registradas para esta semana.
                        </div>
                        <p class="text-gray-400">
                            La semana del {{ $weekStart->format('d/m/Y') }} al {{ $weekEnd->format('d/m/Y') }} no tiene actividades registradas.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
