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

    <style>
        /* Estilos para autocompletado */
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
        
        .autocomplete-no-results {
            padding: 0.75rem 1rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
            font-style: italic;
        }

        .select-with-autocomplete {
            display: none;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Formulario de filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
                    
                    <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <!-- Usuario -->
                            <div class="autocomplete-container">
                                <label for="empleado_search" class="block text-sm font-medium text-gray-700">
                                    Empleado 
                                    <span class="text-xs text-gray-500 ml-1">(con autocompletado)</span>
                                </label>
                                <input type="text" name="empleado_search" id="empleado_search" value="{{ request('user_id') ? $users->where('id', request('user_id'))->first()?->name : '' }}" 
                                       placeholder="Buscar empleado..."
                                       class="autocomplete-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       autocomplete="off">
                                <input type="hidden" name="user_id" id="user_id_hidden" value="{{ request('user_id') }}">
                                <div id="empleado-suggestions" class="autocomplete-suggestions hidden"></div>
                                
                                <!-- Select original oculto como fallback -->
                                <select name="user_id_fallback" id="user_id" class="select-with-autocomplete mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todos los empleados</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dirección -->
                            <div class="autocomplete-container">
                                <label for="direccion_search" class="block text-sm font-medium text-gray-700">
                                    Dirección
                                    <span class="text-xs text-gray-500 ml-1">(con autocompletado)</span>
                                </label>
                                <input type="text" name="direccion_search" id="direccion_search" value="{{ request('direccion') }}" 
                                       placeholder="Buscar dirección..."
                                       class="autocomplete-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       autocomplete="off">
                                <input type="hidden" name="direccion" id="direccion_hidden" value="{{ request('direccion') }}">
                                <div id="direccion-suggestions" class="autocomplete-suggestions hidden"></div>
                                
                                <!-- Select original oculto como fallback -->
                                <select name="direccion_fallback" id="direccion_fallback" class="select-with-autocomplete mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
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
                            <div class="autocomplete-container">
                                <label for="search" class="block text-sm font-medium text-gray-700">
                                    Búsqueda
                                    <span class="text-xs text-gray-500 ml-1">(con autocompletado)</span>
                                </label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Buscar en título, referencia..."
                                       class="autocomplete-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       autocomplete="off">
                                <div id="busqueda-suggestions" class="autocomplete-suggestions hidden"></div>
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
                                return $activity->user->direccion->nombre ?? 'Sin dirección especificada';
                            });
                        @endphp

                        @foreach($groupedActivities as $direccion => $activitiesByDir)
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">
                                    <span class="text-blue-600">{{ $activitiesByDir->first()->user->coordinacion->nombre ?? 'Sin coordinación' }}</span>
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

    <script>
        // Sistema de autocompletado para reportes
        class ReportsAutocompleteManager {
            constructor() {
                this.debounceTimer = null;
                this.activeField = null;
                this.init();
            }

            init() {
                // Configurar autocompletado para empleados
                this.setupAutocomplete('empleado_search', 'empleado-suggestions', '{{ route("api.autocomplete.empleados") }}', 'user_id_hidden');
                
                // Configurar autocompletado para direcciones
                this.setupAutocomplete('direccion_search', 'direccion-suggestions', '{{ route("api.autocomplete.direcciones") }}', 'direccion_hidden');
                
                // Configurar autocompletado para búsqueda
                this.setupAutocomplete('search', 'busqueda-suggestions', '{{ route("api.autocomplete.busqueda") }}');

                // Cerrar sugerencias al hacer clic fuera
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.autocomplete-container')) {
                        this.hideAllSuggestions();
                    }
                });

                // Limpiar campos cuando se presiona el botón de limpiar filtros
                const clearButton = document.querySelector('a[href="{{ route("reports.index") }}"]');
                if (clearButton) {
                    clearButton.addEventListener('click', () => {
                        document.getElementById('empleado_search').value = '';
                        document.getElementById('direccion_search').value = '';
                        document.getElementById('user_id_hidden').value = '';
                        document.getElementById('direccion_hidden').value = '';
                    });
                }
            }

            setupAutocomplete(inputId, suggestionId, apiUrl, hiddenFieldId = null) {
                const input = document.getElementById(inputId);
                const suggestions = document.getElementById(suggestionId);

                if (!input || !suggestions) return;

                input.addEventListener('input', (e) => {
                    const query = e.target.value.trim();
                    
                    // Limpiar campo oculto si existe
                    if (hiddenFieldId) {
                        const hiddenField = document.getElementById(hiddenFieldId);
                        if (hiddenField && query === '') {
                            hiddenField.value = '';
                        }
                    }
                    
                    if (query.length < 2) {
                        this.hideSuggestions(suggestionId);
                        return;
                    }

                    this.debouncedSearch(query, apiUrl, suggestionId, inputId, hiddenFieldId);
                });

                input.addEventListener('focus', (e) => {
                    this.activeField = inputId;
                    const query = e.target.value.trim();
                    if (query.length >= 2) {
                        this.debouncedSearch(query, apiUrl, suggestionId, inputId, hiddenFieldId);
                    }
                });

                input.addEventListener('keydown', (e) => {
                    this.handleKeyNavigation(e, suggestionId, inputId, hiddenFieldId);
                });
            }

            debouncedSearch(query, apiUrl, suggestionId, inputId, hiddenFieldId) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.fetchSuggestions(query, apiUrl, suggestionId, inputId, hiddenFieldId);
                }, 300);
            }

            async fetchSuggestions(query, apiUrl, suggestionId, inputId, hiddenFieldId) {
                try {
                    this.showLoading(suggestionId);
                    
                    const response = await fetch(`${apiUrl}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();
                    this.renderSuggestions(data, suggestionId, inputId, hiddenFieldId, query);
                } catch (error) {
                    console.error('Error fetching suggestions:', error);
                    this.showError(suggestionId);
                }
            }

            renderSuggestions(suggestions, suggestionId, inputId, hiddenFieldId, query) {
                const container = document.getElementById(suggestionId);
                
                if (!suggestions || suggestions.length === 0) {
                    this.showNoResults(suggestionId);
                    return;
                }

                container.innerHTML = '';
                
                suggestions.forEach((suggestion, index) => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    item.dataset.index = index;

                    // Si es empleado (tiene estructura {id, text})
                    if (typeof suggestion === 'object' && suggestion.id) {
                        item.dataset.value = suggestion.id;
                        item.dataset.text = suggestion.text;
                        item.innerHTML = this.highlightMatch(suggestion.text, query);
                    } else {
                        // Para direcciones y búsqueda (strings simples)
                        item.dataset.value = suggestion;
                        item.dataset.text = suggestion;
                        item.innerHTML = this.highlightMatch(suggestion, query);
                    }

                    item.addEventListener('click', () => {
                        const value = item.dataset.value;
                        const text = item.dataset.text;
                        
                        document.getElementById(inputId).value = text;
                        
                        if (hiddenFieldId) {
                            document.getElementById(hiddenFieldId).value = value;
                        }
                        
                        this.hideSuggestions(suggestionId);
                    });

                    container.appendChild(item);
                });

                this.showSuggestions(suggestionId);
            }

            highlightMatch(text, query) {
                if (!query) return text;
                const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
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
                
                setTimeout(() => {
                    this.hideSuggestions(suggestionId);
                }, 3000);
            }

            showNoResults(suggestionId) {
                const container = document.getElementById(suggestionId);
                container.innerHTML = '<div class="autocomplete-no-results">No se encontraron resultados</div>';
                this.showSuggestions(suggestionId);
                
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
                this.hideSuggestions('empleado-suggestions');
                this.hideSuggestions('direccion-suggestions');
                this.hideSuggestions('busqueda-suggestions');
                this.activeField = null;
            }

            handleKeyNavigation(e, suggestionId, inputId, hiddenFieldId) {
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
                            const value = currentActive.dataset.value;
                            const text = currentActive.dataset.text;
                            
                            document.getElementById(inputId).value = text;
                            
                            if (hiddenFieldId) {
                                document.getElementById(hiddenFieldId).value = value;
                            }
                            
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
            new ReportsAutocompleteManager();
        });
    </script>
</x-app-layout>
