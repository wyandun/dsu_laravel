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

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Controles del calendario -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <!-- Selector de empleado (solo para jefes y administradores) -->
                    @if((Auth::user()->isJefe() || Auth::user()->isAdministrador()) && $employees->count() > 0)
                        <div class="flex justify-center mb-4">
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

            <!-- CALENDARIO CON FULLCALENDAR -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="calendar"></div>
                </div>
            </div>

            <!-- Leyenda de colores -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Leyenda</h4>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-blue-500 rounded"></div>
                            <span class="text-sm text-gray-700">Quipux</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-red-500 rounded"></div>
                            <span class="text-sm text-gray-700">Mantis</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-green-500 rounded"></div>
                            <span class="text-sm text-gray-700">CTIT</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-purple-500 rounded"></div>
                            <span class="text-sm text-gray-700">Correo</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-gray-500 rounded"></div>
                            <span class="text-sm text-gray-700">Otros</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/es.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            // Preparar eventos del calendario
            var events = [
                @if($selectedEmployee && $activities->count() > 0)
                    @foreach($activities as $activity)
                        {
                            title: '{{ $activity->titulo }} ({{ number_format($activity->tiempo, 1) }}h)',
                            start: '{{ $activity->fecha_actividad->format('Y-m-d') }}',
                            backgroundColor: getTypeColor('{{ $activity->tipo }}'),
                            borderColor: getTypeColor('{{ $activity->tipo }}'),
                            extendedProps: {
                                tipo: '{{ $activity->tipo }}',
                                numero_referencia: '{{ $activity->numero_referencia }}',
                                tiempo: {{ $activity->tiempo }},
                                observaciones: '{{ addslashes($activity->observaciones) }}'
                            }
                        },
                    @endforeach
                @endif
            ];

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                height: 'auto',
                events: events,
                eventDisplay: 'block',
                dayMaxEvents: 3,
                moreLinkText: 'más',
                eventClick: function(info) {
                    showEventDetails(info.event);
                },
                dateClick: function(info) {
                    // Opcional: manejar clicks en fechas
                    console.log('Clicked on: ' + info.dateStr);
                }
            });

            calendar.render();

            // Función para obtener color según tipo
            function getTypeColor(tipo) {
                switch(tipo) {
                    case 'Quipux': return '#3B82F6'; // blue-500
                    case 'Mantis': return '#EF4444'; // red-500
                    case 'CTIT': return '#10B981'; // green-500
                    case 'Correo': return '#8B5CF6'; // purple-500
                    case 'Otros': return '#6B7280'; // gray-500
                    default: return '#6B7280';
                }
            }

            // Función para mostrar detalles del evento
            function showEventDetails(event) {
                const props = event.extendedProps;
                alert(`
Título: ${event.title}
Tipo: ${props.tipo}
Número de Referencia: ${props.numero_referencia}
Tiempo: ${props.tiempo} horas
Observaciones: ${props.observaciones}
Fecha: ${event.start.toLocaleDateString('es-ES')}
                `.trim());
            }
        });
    </script>
</x-app-layout>
