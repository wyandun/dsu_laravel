<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mis Actividades Diarias') }}
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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @php
                                $totalActivities = $activitiesGrouped->flatten()->count();
                                $totalHours = $activitiesGrouped->flatten()->sum('tiempo');
                                $totalDays = $activitiesGrouped->count();
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
                        </div>
                    </div>
                </div>

                <!-- Actividades agrupadas por fecha -->
                <div class="space-y-6">
                    @foreach($activitiesGrouped as $date => $activities)
                        @php
                            $dateCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
                            $isToday = $dateCarbon->isToday();
                            $dailyHours = $activities->sum('tiempo');
                        @endphp
                        
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                            <!-- Encabezado de fecha -->
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            {{ $dateCarbon->format('l, d \d\e F \d\e Y') }}
                                        </h3>
                                        @if($isToday)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Hoy
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-2 sm:mt-0 flex items-center space-x-4 text-sm text-gray-600">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $activities->count() }} actividades
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ number_format($dailyHours, 2) }} horas
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de actividades del día -->
                            <div class="divide-y divide-gray-200">
                                @foreach($activities->sortBy('created_at') as $activity)
                                    <div class="p-6 hover:bg-gray-50 transition duration-150 ease-in-out">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-3 mb-2">
                                                    <h4 class="text-lg font-medium text-gray-900 truncate">
                                                        {{ $activity->titulo }}
                                                    </h4>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        @php
                                                            $tipoEnum = \App\Enums\ActivityType::from($activity->tipo);
                                                            $color = $tipoEnum->getColor();
                                                        @endphp
                                                        bg-{{ $color }}-100 text-{{ $color }}-800">
                                                        {{ $activity->tipo }}
                                                    </span>
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ number_format($activity->tiempo, 2) }} hrs
                                                    </span>
                                                </div>
                                                
                                                @if($activity->numero_referencia)
                                                    <p class="text-sm text-gray-600 mb-1">
                                                        <strong>Referencia:</strong> {{ $activity->numero_referencia }}
                                                    </p>
                                                @endif
                                                
                                                @if($activity->observaciones)
                                                    <p class="text-sm text-gray-600">
                                                        {{ $activity->observaciones }}
                                                    </p>
                                                @endif
                                                
                                                <p class="text-xs text-gray-500 mt-2">
                                                    Registrado el {{ $activity->created_at->format('d/m/Y \a \l\a\s H:i') }}
                                                </p>
                                            </div>

                                            <!-- Acciones -->
                                            <div class="flex items-center space-x-2 ml-4">
                                                @if($isToday)
                                                    <a href="{{ route('activities.edit', $activity) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                        Editar
                                                    </a>
                                                    <form method="POST" action="{{ route('activities.destroy', $activity) }}" 
                                                          class="inline"
                                                          onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta actividad?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="text-red-600 hover:text-red-900 text-sm font-medium ml-2">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400 text-xs">Solo editable hoy</span>
                                                @endif
                                                
                                                <a href="{{ route('activities.show', $activity) }}" 
                                                   class="text-gray-600 hover:text-gray-900 text-sm font-medium ml-2">
                                                    Ver
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Estado vacío -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <div class="text-gray-500 text-lg mb-4">
                            No tienes actividades registradas para esta semana.
                        </div>
                        <p class="text-gray-400 mb-6">
                            La semana del {{ $weekStart->format('d/m/Y') }} al {{ $weekEnd->format('d/m/Y') }} no tiene actividades registradas.
                        </p>
                        <a href="{{ route('activities.create') }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Nueva Actividad
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
