<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reportes de Actividades') }}
            </h2>
            <button onclick="document.getElementById('exportForm').submit()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Exportar a Excel
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Formulario de filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
                    
                    <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <!-- Usuario -->
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700">Empleado</label>
                                <select name="user_id" id="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todos los empleados</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dirección -->
                            <div>
                                <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                                <select name="direccion" id="direccion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todas las direcciones</option>
                                    @foreach($direcciones as $direccion)
                                        <option value="{{ $direccion }}" {{ request('direccion') == $direccion ? 'selected' : '' }}>
                                            {{ $direccion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                                <select name="tipo" id="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todos los tipos</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Búsqueda -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Búsqueda</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Buscar en título, referencia..."
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <!-- Fecha inicio -->
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <!-- Fecha fin -->
                            <div>
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="flex space-x-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Filtrar
                            </button>
                            <a href="{{ route('reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Limpiar filtros
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $totalActividades }}</div>
                            <div class="text-gray-600">Total de Actividades</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ number_format($totalTiempo, 2) }}</div>
                            <div class="text-gray-600">Total de Horas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de actividades -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($activities->count() > 0)
                        <!-- Agrupamiento por dirección -->
                        @php
                            $groupedActivities = $activities->getCollection()->groupBy(function($activity) {
                                return $activity->user->direccion ?? 'Sin dirección especificada';
                            });
                        @endphp

                        @foreach($groupedActivities as $direccion => $activitiesByDir)
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">
                                    <span class="text-blue-600">{{ $activitiesByDir->first()->user->coordinacion ?? 'Sin coordinación' }}</span>
                                    <br>
                                    <span class="text-sm">{{ $direccion }}</span>
                                    <span class="text-sm text-gray-600">({{ $activitiesByDir->count() }} actividades - {{ number_format($activitiesByDir->sum('tiempo'), 2) }} horas)</span>
                                </h3>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($activitiesByDir as $activity)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $activity->fecha_actividad->format('d/m/Y') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $activity->user->name }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        {{ $activity->titulo }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            @switch($activity->tipo)
                                                                @case('Quipux') bg-blue-100 text-blue-800 @break
                                                                @case('Mantis') bg-red-100 text-red-800 @break
                                                                @case('CTIT') bg-green-100 text-green-800 @break
                                                                @case('Correo') bg-yellow-100 text-yellow-800 @break
                                                                @default bg-gray-100 text-gray-800 @break
                                                            @endswitch
                                                        ">
                                                            {{ $activity->tipo }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $activity->numero_referencia }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $activity->tiempo }}h
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                                        {{ $activity->observaciones }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                        <!-- Paginación -->
                        <div class="mt-6">
                            {{ $activities->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500">No se encontraron actividades con los filtros aplicados.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de exportación (hidden) -->
    <form id="exportForm" method="POST" action="{{ route('reports.export') }}" style="display: none;">
        @csrf
        <input type="hidden" name="user_id" value="{{ request('user_id') }}">
        <input type="hidden" name="direccion" value="{{ request('direccion') }}">
        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="fecha_inicio" value="{{ request('fecha_inicio') }}">
        <input type="hidden" name="fecha_fin" value="{{ request('fecha_fin') }}">
    </form>
</x-app-layout>
