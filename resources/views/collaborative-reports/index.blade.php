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

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Estilos adicionales para autocompletado */
        .autocomplete-container {
            position: relative;
        }
        
        .autocomplete-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 0.375rem 0.375rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            z-index: 50;
            max-height: 15rem;
            overflow-y: auto;
        }
        
        .autocomplete-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s ease-in-out;
        }
        
        .autocomplete-item:last-child {
            border-bottom: none;
        }
        
        .autocomplete-item:hover,
        .autocomplete-item.bg-indigo-100 {
            background-color: #e0e7ff;
        }
        
        .autocomplete-item:hover {
            background-color: #c7d2fe;
        }
        
        /* Mejorar la transición del input cuando está enfocado */
        .autocomplete-input:focus {
            border-radius: 0.375rem 0.375rem 0 0;
        }
        
        .autocomplete-input:focus + .autocomplete-suggestions:not(.hidden) {
            border-top: 1px solid #6366f1;
        }
        /* Indicador de carga */
        .autocomplete-loading {
            padding: 0.75rem 1rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .autocomplete-loading::before {
            content: '';
            width: 1rem;
            height: 1rem;
            border: 2px solid #e5e7eb;
            border-top: 2px solid #6366f1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mensaje cuando no hay resultados */
        .autocomplete-no-results {
            padding: 0.75rem 1rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
            font-style: italic;
        }
    </style>

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
                            <div class="relative autocomplete-container">
                                <label for="numero_referencia" class="block text-sm font-medium text-gray-700">
                                    Número de Referencia
                                    <span class="text-xs text-gray-500 ml-1">(con autocompletado)</span>
                                </label>
                                <input type="text" name="numero_referencia" id="numero_referencia" value="{{ request('numero_referencia') }}" 
                                       placeholder="Buscar por número de referencia..."
                                       class="autocomplete-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       autocomplete="off">
                                <div id="referencia-suggestions" class="autocomplete-suggestions hidden"></div>
                            </div>

                            <!-- Búsqueda -->
                            <div class="relative autocomplete-container">
                                <label for="search" class="block text-sm font-medium text-gray-700">
                                    Búsqueda
                                    <span class="text-xs text-gray-500 ml-1">(con autocompletado)</span>
                                </label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Buscar en título, observaciones..."
                                       class="autocomplete-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       autocomplete="off">
                                <div id="search-suggestions" class="autocomplete-suggestions hidden"></div>
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

                    <!-- Información sobre filtros -->
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Paginación:</strong> Se muestran 10 grupos por página para mejor rendimiento. 
                                    Use los filtros para encontrar grupos específicos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">
                                {{ $collaborativeGroups->currentPage() }} / {{ $collaborativeGroups->lastPage() }}
                            </div>
                            <div class="text-gray-600">Páginas ({{ $collaborativeGroups->count() }} grupos mostrados)</div>
                        </div>
                    </div>

                    @if($collaborativeGroups->hasPages())
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-600">
                                Mostrando {{ $collaborativeGroups->firstItem() }} a {{ $collaborativeGroups->lastItem() }} 
                                de {{ $collaborativeGroups->total() }} grupos colaborativos
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gráficos de Análisis -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Gráfico 1: Horas por Dirección (sin filtro por tipo) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Total de Horas por Dirección</h3>
                        <div style="height: 400px;" class="flex items-center justify-center">
                            <canvas id="chartByDirection"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico 2: Horas por Empleado (todos los empleados) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Total de Horas por Empleado</h3>
                        <div style="height: 400px;" class="flex items-center justify-center">
                            <canvas id="chartByEmployee"></canvas>
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
                                                        {{ $participante->user->coordinacion->nombre ?? 'Sin coordinación' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $participante->user->direccion->nombre ?? 'Sin dirección' }}
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
                                                        $activitiesInGroup = \App\Models\Activity::with(['user.direccion.coordinacion'])
                                                            ->where('tipo', $group->tipo)
                                                            ->where('numero_referencia', $group->numero_referencia)
                                                            ->when(!auth()->user()->isAdministrador() && auth()->user()->isDirector(), function($q) {
                                                                $empleadosIds = auth()->user()->getEmpleadosBajoSupervision()->pluck('id');
                                                                return $q->whereIn('user_id', $empleadosIds);
                                                            })
                                                            ->when(!auth()->user()->isAdministrador() && auth()->user()->isCoordinador(), function($q) {
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
                                                                <div class="text-sm text-gray-500">{{ $activity->user->direccion->nombre ?? 'Sin dirección' }}</div>
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

        // Sistema de autocompletado
        class AutocompleteManager {
            constructor() {
                this.debounceTimer = null;
                this.activeField = null;
                this.init();
            }

            init() {
                // Configurar autocompletado para número de referencia
                this.setupAutocomplete('numero_referencia', 'referencia-suggestions', '{{ route("api.autocomplete.referencia") }}');
                
                // Configurar autocompletado para búsqueda
                this.setupAutocomplete('search', 'search-suggestions', '{{ route("api.autocomplete.titulos") }}');

                // Cerrar sugerencias al hacer clic fuera
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.autocomplete-container')) {
                        this.hideAllSuggestions();
                    }
                });
            }

            setupAutocomplete(inputId, suggestionId, apiUrl) {
                const input = document.getElementById(inputId);
                const suggestions = document.getElementById(suggestionId);

                if (!input || !suggestions) return;

                input.addEventListener('input', (e) => {
                    const query = e.target.value.trim();
                    
                    if (query.length < 2) {
                        this.hideSuggestions(suggestionId);
                        return;
                    }

                    this.debouncedSearch(query, apiUrl, suggestionId, inputId);
                });

                input.addEventListener('focus', (e) => {
                    this.activeField = inputId;
                    const query = e.target.value.trim();
                    if (query.length >= 2) {
                        this.debouncedSearch(query, apiUrl, suggestionId, inputId);
                    }
                });

                input.addEventListener('keydown', (e) => {
                    this.handleKeyNavigation(e, suggestionId, inputId);
                });
            }

            debouncedSearch(query, apiUrl, suggestionId, inputId) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.fetchSuggestions(query, apiUrl, suggestionId, inputId);
                }, 300);
            }

            async fetchSuggestions(query, apiUrl, suggestionId, inputId) {
                try {
                    // Mostrar indicador de carga
                    this.showLoading(suggestionId);
                    
                    const response = await fetch(`${apiUrl}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();
                    this.renderSuggestions(data, suggestionId, inputId);
                } catch (error) {
                    console.error('Error fetching suggestions:', error);
                    this.showError(suggestionId);
                }
            }

            renderSuggestions(suggestions, suggestionId, inputId) {
                const container = document.getElementById(suggestionId);
                const input = document.getElementById(inputId);
                const query = input.value.toLowerCase();
                
                if (!suggestions || suggestions.length === 0) {
                    this.showNoResults(suggestionId);
                    return;
                }

                container.innerHTML = '';
                
                suggestions.forEach((suggestion, index) => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    item.dataset.index = index;
                    item.dataset.originalText = suggestion; // Guardar texto original

                    // Resaltar texto coincidente
                    const highlightedText = this.highlightMatch(suggestion, query);
                    item.innerHTML = highlightedText;

                    item.addEventListener('click', () => {
                        document.getElementById(inputId).value = suggestion;
                        this.hideSuggestions(suggestionId);
                    });

                    container.appendChild(item);
                });

                this.showSuggestions(suggestionId);
            }

            highlightMatch(text, query) {
                if (!query) return text;
                
                const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                return text.replace(regex, '<strong class="text-indigo-600">$1</strong>');
            }

            showLoading(suggestionId) {
                const container = document.getElementById(suggestionId);
                container.innerHTML = '<div class="autocomplete-loading">Buscando...</div>';
                this.showSuggestions(suggestionId);
            }

            showError(suggestionId) {
                const container = document.getElementById(suggestionId);
                container.innerHTML = '<div class="autocomplete-no-results">Error al buscar sugerencias</div>';
                this.showSuggestions(suggestionId);
                
                // Ocultar después de 3 segundos
                setTimeout(() => {
                    this.hideSuggestions(suggestionId);
                }, 3000);
            }

            showNoResults(suggestionId) {
                const container = document.getElementById(suggestionId);
                container.innerHTML = '<div class="autocomplete-no-results">No se encontraron resultados</div>';
                this.showSuggestions(suggestionId);
                
                // Ocultar después de 2 segundos
                setTimeout(() => {
                    this.hideSuggestions(suggestionId);
                }, 2000);
            }

            showSuggestions(suggestionId) {
                const container = document.getElementById(suggestionId);
                container.classList.remove('hidden');
            }

            hideSuggestions(suggestionId) {
                const container = document.getElementById(suggestionId);
                container.classList.add('hidden');
                container.innerHTML = '';
            }

            hideAllSuggestions() {
                this.hideSuggestions('referencia-suggestions');
                this.hideSuggestions('search-suggestions');
                this.activeField = null;
            }

            handleKeyNavigation(e, suggestionId, inputId) {
                const container = document.getElementById(suggestionId);
                const items = container.querySelectorAll('div[data-index]');
                
                if (items.length === 0) return;

                const currentActive = container.querySelector('.bg-indigo-100');
                let newActiveIndex = -1;

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (currentActive) {
                            newActiveIndex = parseInt(currentActive.dataset.index) + 1;
                            currentActive.classList.remove('bg-indigo-100');
                        } else {
                            newActiveIndex = 0;
                        }
                        if (newActiveIndex >= items.length) newActiveIndex = 0;
                        break;

                    case 'ArrowUp':
                        e.preventDefault();
                        if (currentActive) {
                            newActiveIndex = parseInt(currentActive.dataset.index) - 1;
                            currentActive.classList.remove('bg-indigo-100');
                        } else {
                            newActiveIndex = items.length - 1;
                        }
                        if (newActiveIndex < 0) newActiveIndex = items.length - 1;
                        break;

                    case 'Enter':
                        e.preventDefault();
                        if (currentActive) {
                            const value = currentActive.dataset.originalText || currentActive.textContent;
                            document.getElementById(inputId).value = value;
                            this.hideSuggestions(suggestionId);
                        }
                        break;

                    case 'Escape':
                        this.hideSuggestions(suggestionId);
                        break;
                }

                if (newActiveIndex >= 0 && items[newActiveIndex]) {
                    items[newActiveIndex].classList.add('bg-indigo-100');
                }
            }
        }

        // Inicializar autocompletado cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            new AutocompleteManager();
            initCharts();
        });

        // Inicializar gráficos
        function initCharts() {
            let chartByDirection = null;
            let chartByEmployee = null;

            // Función para cargar gráfico por dirección (respeta TODOS los filtros)
            function loadDirectionChart() {
                const params = new URLSearchParams({
                    numero_referencia: '{{ request("numero_referencia") }}',
                    search: '{{ request("search") }}',
                    fecha_inicio: '{{ request("fecha_inicio") }}',
                    fecha_fin: '{{ request("fecha_fin") }}'
                });

                fetch(`{{ route('collaborative-reports.chart.hours-by-direction') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById('chartByDirection').getContext('2d');
                        
                        if (chartByDirection) {
                            chartByDirection.destroy();
                        }

                        chartByDirection = new Chart(ctx, {
                            type: 'pie',
                            data: data,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: data.title,
                                        font: {
                                            size: 16,
                                            weight: 'bold'
                                        }
                                    },
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 20,
                                            usePointStyle: true
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const value = context.parsed;
                                                const total = data.total;
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return `${context.label}: ${value}h (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error loading direction chart:', error);
                        const ctx = document.getElementById('chartByDirection').getContext('2d');
                        ctx.fillStyle = '#6B7280';
                        ctx.font = '16px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText('Error al cargar gráfico', ctx.canvas.width / 2, ctx.canvas.height / 2);
                    });
            }

            // Función para cargar gráfico por empleado (respeta TODOS los filtros)
            function loadEmployeeChart() {
                const params = new URLSearchParams({
                    numero_referencia: '{{ request("numero_referencia") }}',
                    search: '{{ request("search") }}',
                    fecha_inicio: '{{ request("fecha_inicio") }}',
                    fecha_fin: '{{ request("fecha_fin") }}'
                });

                fetch(`{{ route('collaborative-reports.chart.hours-by-employee') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById('chartByEmployee').getContext('2d');
                        
                        if (chartByEmployee) {
                            chartByEmployee.destroy();
                        }

                        chartByEmployee = new Chart(ctx, {
                            type: 'pie',
                            data: data,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: data.title,
                                        font: {
                                            size: 16,
                                            weight: 'bold'
                                        }
                                    },
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 20,
                                            usePointStyle: true
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const value = context.parsed;
                                                const total = data.total;
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return `${context.label}: ${value}h (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error loading employee chart:', error);
                        const ctx = document.getElementById('chartByEmployee').getContext('2d');
                        ctx.fillStyle = '#6B7280';
                        ctx.font = '16px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText('Error al cargar gráfico', ctx.canvas.width / 2, ctx.canvas.height / 2);
                    });
            }

            // Cargar gráficos iniciales
            loadDirectionChart();
            loadEmployeeChart();
        }
    </script>
</x-app-layout>
