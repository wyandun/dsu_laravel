@use('Carbon\Carbon')
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
                </a>
                @if(Auth::user()->isJefe() || Auth::user()->isAdministrador())
                    <a href="{{ route('reports.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Reportes
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <style>
        .calendar-container {
            max-width: 100%;
            overflow-x: auto;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .calendar-header {
            background-color: #4f46e5;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .calendar-day {
            border: 1px solid #e5e7eb;
            min-height: 120px;
            padding: 8px;
            background-color: white;
            position: relative;
        }
        
        .calendar-day.other-month {
            background-color: #f9fafb;
            color: #9ca3af;
        }
        
        .calendar-day.today {
            background-color: #fef3c7;
            border-color: #f59e0b;
        }
        
        .day-number {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .activity-item {
            font-size: 11px;
            padding: 2px 6px;
            margin-bottom: 2px;
            border-radius: 3px;
            color: white;
            cursor: pointer;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .activity-item:hover {
            opacity: 0.8;
        }
        
        .activity-helpdesk { background-color: #f97316; }
        .activity-reunion { background-color: #8b5cf6; }
        .activity-quipux { background-color: #3b82f6; }
        .activity-ctit { background-color: #10b981; }
        .activity-sga { background-color: #14b8a6; }
        .activity-correo { background-color: #eab308; }
        .activity-contrato { background-color: #ef4444; }
        .activity-oficio { background-color: #6366f1; }
        .activity-gpr { background-color: #ec4899; }
        .activity-otros { background-color: #6b7280; }
        
        .more-activities {
            font-size: 10px;
            text-align: center;
            color: #6b7280;
            background-color: #f3f4f6;
            padding: 2px;
            border-radius: 3px;
            cursor: pointer;
            border: 1px solid #d1d5db;
        }
        
        .more-activities:hover {
            background-color: #e5e7eb;
        }
        
        .hours-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background-color: #e0e7ff;
            color: #3730a3;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
            border-radius: 8px 8px 0 0;
        }

        .modal-body {
            padding: 20px 24px;
        }

        .activity-detail {
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 6px;
            border-left: 4px solid;
        }

        .activity-detail.helpdesk { border-left-color: #f97316; background-color: #fff7ed; }
        .activity-detail.reunion { border-left-color: #8b5cf6; background-color: #faf5ff; }
        .activity-detail.quipux { border-left-color: #3b82f6; background-color: #eff6ff; }
        .activity-detail.ctit { border-left-color: #10b981; background-color: #f0fdf4; }
        .activity-detail.sga { border-left-color: #14b8a6; background-color: #f0fdfa; }
        .activity-detail.correo { border-left-color: #eab308; background-color: #fefce8; }
        .activity-detail.contrato { border-left-color: #ef4444; background-color: #fef2f2; }
        .activity-detail.oficio { border-left-color: #6366f1; background-color: #eef2ff; }
        .activity-detail.gpr { border-left-color: #ec4899; background-color: #fdf2f8; }
        .activity-detail.otros { border-left-color: #6b7280; background-color: #f9fafb; }

        /* Autocompletado styles */
        .employee-option:hover {
            background-color: #f3f4f6;
        }
        
        .employee-option.selected {
            background-color: #e0e7ff;
        }
        
        #employee_dropdown {
            border-top: none;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        
        #employee_search:focus + #employee_dropdown {
            display: block;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Controles del calendario -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <!-- Selector de empleado con autocompletado (solo para jefes y administradores) -->
                    @if((Auth::user()->isJefe() || Auth::user()->isAdministrador()) && $employees->count() > 0)
                        <div class="flex justify-center mb-4">
                            <form method="GET" action="{{ route('calendar.index') }}" id="employeeForm" class="flex items-center space-x-4">
                                <input type="hidden" name="month" value="{{ $month->format('Y-m-d') }}">
                                <input type="hidden" name="employee_id" id="selected_employee_id" value="{{ $selectedEmployee?->id }}">
                                
                                <label for="employee_search" class="text-sm font-medium text-gray-700">
                                    Empleado:
                                </label>
                                
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="employee_search" 
                                        name="employee_search"
                                        value="{{ $selectedEmployee ? $selectedEmployee->name . ' - ' . ($selectedEmployee->direccion->nombre ?? 'Sin dirección') : '' }}"
                                        placeholder="Buscar empleado..."
                                        class="w-80 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        autocomplete="off">
                                    
                                    <!-- Lista desplegable de empleados -->
                                    <div id="employee_dropdown" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-md shadow-lg z-10 hidden max-h-60 overflow-y-auto">
                                        @foreach($employees as $employee)
                                            <div class="employee-option px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                                                 data-id="{{ $employee->id }}"
                                                 data-name="{{ $employee->name }}"
                                                 data-direccion="{{ $employee->direccion->nombre ?? 'Sin dirección' }}"
                                                 data-full-text="{{ $employee->name }} - {{ $employee->direccion->nombre ?? 'Sin dirección' }}">
                                                <div class="font-medium text-gray-900">{{ $employee->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $employee->direccion->nombre ?? 'Sin dirección' }}</div>
                                                @if($employee->cargo)
                                                    <div class="text-xs text-gray-400">{{ $employee->cargo }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <button type="button" onclick="clearEmployeeSelection()" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-md text-sm text-gray-700">
                                    Limpiar
                                </button>
                            </form>
                        </div>
                    @endif

                    @if(!$selectedEmployee && (Auth::user()->isJefe() || Auth::user()->isAdministrador()))
                        <div class="text-center mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-yellow-800">
                                Selecciona un empleado arriba para ver sus actividades en el calendario.
                            </p>
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
            @endif

            <!-- CALENDARIO -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Control de navegación del mes -->
                    <div class="flex justify-between items-center mb-6">
                        @if($selectedEmployee)
                            <a href="{{ route('calendar.index', ['employee_id' => $selectedEmployee->id, 'month' => $month->copy()->subMonth()->month, 'year' => $month->copy()->subMonth()->year]) }}" 
                               class="flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">
                                ← Mes Anterior
                            </a>
                        @else
                            <a href="{{ route('calendar.index', ['month' => $month->copy()->subMonth()->month, 'year' => $month->copy()->subMonth()->year]) }}" 
                               class="flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">
                                ← Mes Anterior
                            </a>
                        @endif
                        
                        <h3 class="text-xl font-semibold text-gray-800">
                            {{ $month->translatedFormat('F Y') }}
                        </h3>
                        
                        @if($selectedEmployee)
                            <a href="{{ route('calendar.index', ['employee_id' => $selectedEmployee->id, 'month' => $month->copy()->addMonth()->month, 'year' => $month->copy()->addMonth()->year]) }}" 
                               class="flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">
                                Mes Siguiente →
                            </a>
                        @else
                            <a href="{{ route('calendar.index', ['month' => $month->copy()->addMonth()->month, 'year' => $month->copy()->addMonth()->year]) }}" 
                               class="flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">
                                Mes Siguiente →
                            </a>
                        @endif
                    </div>

                    <!-- Información del empleado seleccionado -->
                    @if($selectedEmployee)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-blue-800 text-sm">
                                <strong>Mostrando actividades de:</strong> {{ $selectedEmployee->name }}
                                @if($activities->count() > 0)
                                    ({{ $activities->count() }} actividades, {{ number_format($activities->sum('tiempo'), 1) }} horas total)
                                @else
                                    (Sin actividades este mes)
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                            <p class="text-yellow-800 text-sm">
                                <strong>Selecciona un empleado arriba</strong> para ver sus actividades en el calendario.
                            </p>
                        </div>
                    @endif

                    <!-- Calendario -->
                    <div class="calendar-container">
                        @php
                            $today = now();
                            $firstDay = $month->copy()->startOfMonth();
                            $startWeek = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
                            
                            // Agrupar actividades por fecha
                            $activitiesByDate = $activities->groupBy(function($activity) {                            return $activity->fecha_actividad->format('Y-m-d');
                        });
                        
                        if (!function_exists('getActivityClass')) {
                            function getActivityClass($type) {
                                switch($type) {
                                    case 'Helpdesk': return 'activity-helpdesk';
                                    case 'Reunión': return 'activity-reunion';
                                    case 'Quipux': return 'activity-quipux';
                                    case 'CTIT': return 'activity-ctit';
                                    case 'SGA': return 'activity-sga';
                                    case 'Correo': return 'activity-correo';
                                    case 'Contrato': return 'activity-contrato';
                                    case 'Oficio': return 'activity-oficio';
                                    case 'GPR': return 'activity-gpr';
                                    case 'Otros': return 'activity-otros';
                                    default: return 'activity-otros';
                                }
                            }
                        }
                        @endphp
                        
                        <div class="calendar-grid">
                            <!-- Encabezados -->
                            <div class="calendar-header">Lunes</div>
                            <div class="calendar-header">Martes</div>
                            <div class="calendar-header">Miércoles</div>
                            <div class="calendar-header">Jueves</div>
                            <div class="calendar-header">Viernes</div>
                            <div class="calendar-header">Sábado</div>
                            <div class="calendar-header">Domingo</div>
                            
                            <!-- Días del mes -->
                            @php $currentDate = $startWeek->copy(); @endphp
                            @for($week = 0; $week < 6; $week++)
                                @for($day = 0; $day < 7; $day++)
                                    @php
                                        $dateKey = $currentDate->format('Y-m-d');
                                        $dayActivities = $activitiesByDate->get($dateKey, collect());
                                        $isCurrentMonth = $currentDate->month === $month->month;
                                        $isToday = $currentDate->isToday();
                                        $totalHours = $dayActivities->sum('tiempo');
                                    @endphp
                                    
                                    <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                                        <!-- Número del día -->
                                        <div class="day-number">{{ $currentDate->day }}</div>
                                        
                                        <!-- Badge de horas totales -->
                                        @if($totalHours > 0)
                                            <div class="hours-badge">{{ number_format($totalHours, 1) }}h</div>
                                        @endif
                                        
                                        <!-- Actividades del día -->
                                        @if($selectedEmployee && $dayActivities->count() > 0)
                                            @foreach($dayActivities->take(4) as $activity)
                                                <div class="activity-item {{ getActivityClass($activity->tipo) }}"
                                                     title="{{ $activity->titulo }} ({{ number_format($activity->tiempo, 1) }}h) - {{ $activity->tipo }}"
                                                     onclick="showActivityDetails('{{ addslashes($activity->titulo) }}', '{{ $activity->tipo }}', '{{ addslashes($activity->numero_referencia ?? '') }}', '{{ $activity->tiempo }}', '{{ addslashes($activity->observaciones ?? '') }}', '{{ $activity->fecha_actividad->format('d/m/Y') }}')">
                                                    {{ Str::limit($activity->titulo, 20) }}
                                                </div>
                                            @endforeach
                                            
                                            @if($dayActivities->count() > 4)
                                                <div class="more-activities"
                                                     onclick="showAllActivities('{{ $currentDate->format('d/m/Y') }}', [
                                                        @foreach($dayActivities as $act)
                                                            {
                                                                'titulo': '{{ addslashes($act->titulo) }}',
                                                                'tipo': '{{ $act->tipo }}',
                                                                'tiempo': {{ $act->tiempo }},
                                                                'numero_referencia': '{{ addslashes($act->numero_referencia ?? '') }}',
                                                                'observaciones': '{{ addslashes($act->observaciones ?? '') }}'
                                                            }@if(!$loop->last),@endif
                                                        @endforeach
                                                     ])">
                                                    +{{ $dayActivities->count() - 4 }} más
                                                </div>
                                            @elseif($dayActivities->count() > 2)
                                                <div class="more-activities"
                                                     onclick="showAllActivities('{{ $currentDate->format('d/m/Y') }}', [
                                                        @foreach($dayActivities as $act)
                                                            {
                                                                'titulo': '{{ addslashes($act->titulo) }}',
                                                                'tipo': '{{ $act->tipo }}',
                                                                'tiempo': {{ $act->tiempo }},
                                                                'numero_referencia': '{{ addslashes($act->numero_referencia ?? '') }}',
                                                                'observaciones': '{{ addslashes($act->observaciones ?? '') }}'
                                                            }@if(!$loop->last),@endif
                                                        @endforeach
                                                     ])">
                                                    Ver todas ({{ $dayActivities->count() }})
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    
                                    @php $currentDate->addDay(); @endphp
                                @endfor
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leyenda de colores -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Leyenda de Tipos de Actividades</h4>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #f97316;"></div>
                            <span class="text-sm text-gray-700">Helpdesk</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #8b5cf6;"></div>
                            <span class="text-sm text-gray-700">Reunión</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #3b82f6;"></div>
                            <span class="text-sm text-gray-700">Quipux</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #10b981;"></div>
                            <span class="text-sm text-gray-700">CTIT</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #14b8a6;"></div>
                            <span class="text-sm text-gray-700">SGA</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #eab308;"></div>
                            <span class="text-sm text-gray-700">Correo</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #ef4444;"></div>
                            <span class="text-sm text-gray-700">Contrato</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #6366f1;"></div>
                            <span class="text-sm text-gray-700">Oficio</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #ec4899;"></div>
                            <span class="text-sm text-gray-700">GPR</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded" style="background-color: #6b7280;"></div>
                            <span class="text-sm text-gray-700">Otros</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar todas las actividades del día -->
    <div id="activitiesModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex justify-between items-center">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900"></h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <div id="modalContent"></div>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar detalles de una actividad
        function showActivityDetails(titulo, tipo, referencia, tiempo, observaciones, fecha) {
            const details = `📋 DETALLES DE LA ACTIVIDAD

🎯 Título: ${titulo}
📝 Tipo: ${tipo}
🔗 Referencia: ${referencia || 'N/A'}
⏱️ Tiempo: ${tiempo} horas
📄 Observaciones: ${observaciones || 'N/A'}
📅 Fecha: ${fecha}`;
            alert(details);
        }

        // Función para mostrar todas las actividades de un día en un modal
        function showAllActivities(fecha, actividades) {
            console.log('showAllActivities called with:', fecha, actividades);
            
            // Verificar si actividades es un array válido
            if (!Array.isArray(actividades)) {
                console.error('Actividades no es un array válido:', actividades);
                alert('Error al cargar las actividades del día.');
                return;
            }
            
            const modal = document.getElementById('activitiesModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');
            
            // Configurar título
            modalTitle.textContent = `Actividades del ${fecha}`;
            
            // Generar contenido
            let content = '';
            let totalHours = 0;
            
            actividades.forEach((activity, index) => {
                totalHours += parseFloat(activity.tiempo);
                const typeClass = activity.tipo.toLowerCase().replace('ó', 'o'); // Para "reunión" -> "reunion"
                
                content += `
                    <div class="activity-detail ${typeClass}">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-semibold text-gray-900">${activity.titulo}</h4>
                            <span class="text-sm font-medium text-gray-600">${activity.tiempo}h</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <div class="flex items-center space-x-4 mb-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    ${activity.tipo}
                                </span>
                                ${activity.numero_referencia ? 
                                    `<span class="text-gray-500">Ref: ${activity.numero_referencia}</span>` : 
                                    ''
                                }
                            </div>
                            ${activity.observaciones ? 
                                `<p class="mt-2 text-gray-700">${activity.observaciones}</p>` : 
                                ''
                            }
                        </div>
                    </div>
                `;
            });
            
            // Agregar resumen
            content = `
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-indigo-800 font-medium">Total de actividades: ${actividades.length}</span>
                        <span class="text-indigo-800 font-medium">Total de horas: ${totalHours.toFixed(1)}h</span>
                    </div>
                </div>
                ${content}
            `;
            
            modalContent.innerHTML = content;
            modal.style.display = 'flex';
            
            // Cerrar modal al hacer clic fuera
            modal.onclick = function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            };
        }

        // Función para cerrar el modal
        function closeModal() {
            const modal = document.getElementById('activitiesModal');
            modal.style.display = 'none';
        }

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Calendario cargado correctamente');
            initializeEmployeeAutocomplete();
        });

        // Función para inicializar el autocompletado de empleados
        function initializeEmployeeAutocomplete() {
            const searchInput = document.getElementById('employee_search');
            const dropdown = document.getElementById('employee_dropdown');
            const employeeIdInput = document.getElementById('selected_employee_id');
            const form = document.getElementById('employeeForm');
            
            if (!searchInput || !dropdown) return;
            
            // Mostrar/ocultar dropdown al hacer focus/blur
            searchInput.addEventListener('focus', function() {
                dropdown.classList.remove('hidden');
                filterEmployees(''); // Mostrar todos al hacer focus
            });
            
            // Filtrar empleados mientras se escribe
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                filterEmployees(query);
                dropdown.classList.remove('hidden');
            });
            
            // Ocultar dropdown al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
            
            // Manejar clics en opciones
            dropdown.addEventListener('click', function(e) {
                const option = e.target.closest('.employee-option');
                if (option) {
                    selectEmployee(option);
                }
            });
            
            // Manejar navegación con teclado
            searchInput.addEventListener('keydown', function(e) {
                const visibleOptions = dropdown.querySelectorAll('.employee-option:not(.hidden)');
                const selectedOption = dropdown.querySelector('.employee-option.selected');
                let selectedIndex = Array.from(visibleOptions).indexOf(selectedOption);
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, visibleOptions.length - 1);
                        updateSelection(visibleOptions, selectedIndex);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, 0);
                        updateSelection(visibleOptions, selectedIndex);
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (selectedOption) {
                            selectEmployee(selectedOption);
                        }
                        break;
                    case 'Escape':
                        dropdown.classList.add('hidden');
                        break;
                }
            });
        }
        
        function filterEmployees(query) {
            const options = document.querySelectorAll('.employee-option');
            let visibleCount = 0;
            
            options.forEach(option => {
                const fullText = option.dataset.fullText.toLowerCase();
                const name = option.dataset.name.toLowerCase();
                const direccion = option.dataset.direccion.toLowerCase();
                
                if (fullText.includes(query) || name.includes(query) || direccion.includes(query)) {
                    option.classList.remove('hidden');
                    visibleCount++;
                } else {
                    option.classList.add('hidden');
                    option.classList.remove('selected');
                }
            });
            
            // Auto-seleccionar el primero si hay resultados
            if (visibleCount > 0) {
                const firstVisible = document.querySelector('.employee-option:not(.hidden)');
                if (firstVisible) {
                    document.querySelectorAll('.employee-option').forEach(opt => opt.classList.remove('selected'));
                    firstVisible.classList.add('selected');
                }
            }
        }
        
        function updateSelection(visibleOptions, selectedIndex) {
            document.querySelectorAll('.employee-option').forEach(opt => opt.classList.remove('selected'));
            if (visibleOptions[selectedIndex]) {
                visibleOptions[selectedIndex].classList.add('selected');
                visibleOptions[selectedIndex].scrollIntoView({ block: 'nearest' });
            }
        }
        
        function selectEmployee(option) {
            const searchInput = document.getElementById('employee_search');
            const dropdown = document.getElementById('employee_dropdown');
            const employeeIdInput = document.getElementById('selected_employee_id');
            const form = document.getElementById('employeeForm');
            
            // Actualizar valores
            searchInput.value = option.dataset.fullText;
            employeeIdInput.value = option.dataset.id;
            
            // Ocultar dropdown
            dropdown.classList.add('hidden');
            
            // Enviar formulario automáticamente
            form.submit();
        }
        
        function clearEmployeeSelection() {
            const searchInput = document.getElementById('employee_search');
            const employeeIdInput = document.getElementById('selected_employee_id');
            const form = document.getElementById('employeeForm');
            
            searchInput.value = '';
            employeeIdInput.value = '';
            form.submit();
        }
    </script>
</x-app-layout>
