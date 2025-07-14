<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Actividades del') }} {{ $selectedDate->format('l, d/m/Y') }}
                @if($selectedEmployee)
                    - {{ $selectedEmployee->name }}
                @endif
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('calendar.index', ['employee_id' => $selectedEmployee->id]) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    ← Volver al Calendario
                </a>
                @if($selectedDate->isToday() && $selectedEmployee->id === Auth::id())
                    <a href="{{ route('activities.create') }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Nueva Actividad
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($activities->count() > 0)
                <!-- Resumen del día -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @php
                                $totalActivities = $activities->count();
                                $totalHours = $activities->sum('tiempo');
                                $activitiesByType = $activities->groupBy('tipo');
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
                                <div class="text-3xl font-bold text-purple-600">{{ $activitiesByType->count() }}</div>
                                <div class="text-gray-600">Tipos de Actividad</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribución por tipo -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribución por Tipo</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($activitiesByType as $tipo => $typeActivities)
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($tipo)
                                                @case('Quipux') bg-blue-100 text-blue-800 @break
                                                @case('Mantis') bg-red-100 text-red-800 @break
                                                @case('CTIT') bg-green-100 text-green-800 @break
                                                @case('Correo') bg-yellow-100 text-yellow-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ $tipo }}
                                        </span>
                                        <span class="text-sm text-gray-600">{{ $typeActivities->count() }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Total: {{ number_format($typeActivities->sum('tiempo'), 2) }} horas
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Lista detallada de actividades -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actividades del Día</h3>
                        
                        <div class="space-y-4">
                            @foreach($activities as $activity)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h4 class="text-lg font-medium text-gray-900">
                                                    {{ $activity->titulo }}
                                                </h4>
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
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                                <div>
                                                    <strong>Referencia:</strong> {{ $activity->numero_referencia ?? 'N/A' }}
                                                </div>
                                                <div>
                                                    <strong>Tiempo:</strong> {{ number_format($activity->tiempo, 2) }} horas
                                                </div>
                                                @if($activity->observaciones)
                                                    <div class="md:col-span-2">
                                                        <strong>Observaciones:</strong> 
                                                        <p class="mt-1">{{ $activity->observaciones }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Acciones -->
                                        <div class="ml-4 flex-shrink-0">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('activities.show', $activity) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                    Ver
                                                </a>
                                                @if($activity->user_id === Auth::id())
                                                    @if(Auth::user()->isJefe() || $activity->isToday())
                                                        <a href="{{ route('activities.edit', $activity) }}" 
                                                           class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                            Editar
                                                        </a>
                                                        <form action="{{ route('activities.destroy', $activity) }}" 
                                                              method="POST" 
                                                              class="inline"
                                                              onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta actividad?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                                Eliminar
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-gray-400 text-xs">Solo editable hoy</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <!-- No hay actividades -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="text-gray-500 text-lg mb-4">
                            No hay actividades registradas para este día.
                        </div>
                        <p class="text-gray-400 mb-6">
                            {{ $selectedEmployee->name }} no registró actividades el {{ $selectedDate->format('d/m/Y') }}.
                        </p>
                        
                        @if($selectedDate->isToday() && $selectedEmployee->id === Auth::id())
                            <a href="{{ route('activities.create') }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Registrar Primera Actividad
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
