<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reporte Colaborativo - Actividades por Tipo y Referencia') }}
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
                    
                    <form method="GET" action="{{ route('collaborative-reports.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
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

                            <!-- Número de Referencia -->
                            <div>
                                <label for="numero_referencia" class="block text-sm font-medium text-gray-700">Número de Referencia</label>
                                <select name="numero_referencia" id="numero_referencia" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todas las referencias</option>
                                    @foreach($referencias as $referencia)
                                        <option value="{{ $referencia }}" {{ request('numero_referencia') == $referencia ? 'selected' : '' }}>
                                            {{ $referencia }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Búsqueda -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Búsqueda</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Buscar en título, observaciones..."
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
                            <a href="{{ route('collaborative-reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Limpiar filtros
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $totalGrupos }}</div>
                            <div class="text-gray-600">Grupos Colaborativos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ $totalActividades }}</div>
                            <div class="text-gray-600">Total de Actividades</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ number_format($totalTiempo, 2) }}</div>
                            <div class="text-gray-600">Total de Horas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grupos colaborativos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($collaborativeGroups->count() > 0)
                        <div class="space-y-8">
                            @foreach($collaborativeGroups as $group)
                                <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                                    <!-- Encabezado del grupo -->
                                    <div class="mb-6">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-800">
                                                    <span class="text-blue-600">{{ $group->tipo }}</span> - 
                                                    <span class="text-purple-600">{{ $group->numero_referencia }}</span>
                                                </h3>
                                            </div>
                                            <div class="flex flex-wrap gap-4 text-sm text-gray-600 mt-2 md:mt-0">
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                    <strong>{{ $group->total_actividades }}</strong> actividades
                                                </span>
                                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">
                                                    <strong>{{ $group->total_participantes }}</strong> participantes
                                                </span>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                                    <strong>{{ number_format($group->total_tiempo, 2) }}</strong> horas
                                                </span>
                                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">
                                                    {{ \Carbon\Carbon::parse($group->fecha_inicio)->format('d/m/Y') }} - 
                                                    {{ \Carbon\Carbon::parse($group->fecha_fin)->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lista de participantes -->
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-gray-700 mb-3">Participantes:</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($group->participantes as $participante)
                                                <div class="bg-white p-4 rounded-lg border border-gray-200">
                                                    <div class="font-medium text-gray-800">{{ $participante->user->name }}</div>
                                                    <div class="text-sm text-gray-600">
                                                        {{ $participante->user->coordinacion ?? 'Sin coordinación' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $participante->user->direccion ?? 'Sin dirección' }}
                                                    </div>
                                                    <div class="mt-2 flex space-x-4 text-xs">
                                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                            {{ $participante->actividades_count }} actividades
                                                        </span>
                                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                                            {{ number_format($participante->tiempo_total, 2) }} hrs
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Botón para ver detalles -->
                                    <div class="text-center">
                                        <button onclick="toggleDetails('group-{{ $loop->index }}')" 
                                                class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                            <span id="toggle-text-{{ $loop->index }}">Ver Actividades Detalladas</span>
                                        </button>
                                    </div>

                                    <!-- Actividades detalladas (ocultas por defecto) -->
                                    <div id="group-{{ $loop->index }}" class="hidden mt-6">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @php
                                                        // Obtener las actividades para este grupo
                                                        $activitiesInGroup = \App\Models\Activity::with('user')
                                                            ->where('tipo', $group->tipo)
                                                            ->where('numero_referencia', $group->numero_referencia)
                                                            ->when(auth()->user()->isDirector(), function($q) {
                                                                $empleadosIds = auth()->user()->getEmpleadosBajoSupervision()->pluck('id');
                                                                return $q->whereIn('user_id', $empleadosIds);
                                                            })
                                                            ->when(auth()->user()->isCoordinador(), function($q) {
                                                                $empleadosIds = auth()->user()->getEmpleadosBajoSupervision()->pluck('id');
                                                                return $q->whereIn('user_id', $empleadosIds);
                                                            })
                                                            ->orderBy('fecha_actividad')
                                                            ->get();
                                                    @endphp
                                                    @foreach($activitiesInGroup as $activity)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                {{ $activity->fecha_actividad->format('d/m/Y') }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="text-sm font-medium text-gray-900">{{ $activity->user->name }}</div>
                                                                <div class="text-sm text-gray-500">{{ $activity->user->coordinacion }}</div>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                                {{ $activity->titulo }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                {{ number_format((float)$activity->tiempo, 2) }} hrs
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
                                </div>
                            @endforeach
                        </div>

                        <!-- Paginación -->
                        <div class="mt-6">
                            {{ $collaborativeGroups->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 text-lg">No se encontraron actividades colaborativas.</div>
                            <div class="text-gray-400 text-sm mt-2">
                                Las actividades colaborativas son aquellas que tienen un número de referencia válido.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario oculto para exportar -->
    <form id="exportForm" method="GET" action="{{ route('collaborative-reports.export') }}" style="display: none;">
        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        <input type="hidden" name="numero_referencia" value="{{ request('numero_referencia') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="fecha_inicio" value="{{ request('fecha_inicio') }}">
        <input type="hidden" name="fecha_fin" value="{{ request('fecha_fin') }}">
    </form>

    <script>
        function toggleDetails(groupId) {
            const detailsDiv = document.getElementById(groupId);
            const toggleButton = document.querySelector(`button[onclick="toggleDetails('${groupId}')"]`);
            const toggleText = document.getElementById('toggle-text-' + groupId.split('-')[1]);
            
            if (detailsDiv.classList.contains('hidden')) {
                detailsDiv.classList.remove('hidden');
                toggleText.textContent = 'Ocultar Actividades Detalladas';
                toggleButton.classList.remove('bg-indigo-500', 'hover:bg-indigo-700');
                toggleButton.classList.add('bg-red-500', 'hover:bg-red-700');
            } else {
                detailsDiv.classList.add('hidden');
                toggleText.textContent = 'Ver Actividades Detalladas';
                toggleButton.classList.remove('bg-red-500', 'hover:bg-red-700');
                toggleButton.classList.add('bg-indigo-500', 'hover:bg-indigo-700');
            }
        }
    </script>
</x-app-layout>
