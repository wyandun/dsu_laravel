<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle de Actividad') }}
            </h2>
            <a href="{{ route('activities.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información de la Actividad</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Empleado</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $activity->user->name }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Título</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $activity->titulo }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $activity->tipo }}
                                </span>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Número de Referencia</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $activity->numero_referencia ?: 'N/A' }}</p>
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Tiempo</label>
                                <p class="mt-1 text-sm text-gray-900">{{ number_format($activity->tiempo, 2) }} horas</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Fecha de Actividad</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $activity->fecha_actividad->format('d/m/Y') }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Fecha de Registro</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $activity->created_at->format('d/m/Y H:i') }}</p>
                            </div>

                            @if($activity->updated_at != $activity->created_at)
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Última Modificación</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $activity->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($activity->observaciones)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                <p class="text-sm text-gray-900">{{ $activity->observaciones }}</p>
                            </div>
                        </div>
                    @endif

                    @if($activity->user_id === Auth::id())
                        <div class="mt-6 flex space-x-4">
                            @if(Auth::user()->isJefe() || $activity->isToday())
                                <a href="{{ route('activities.edit', $activity) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Editar
                                </a>
                                <form action="{{ route('activities.destroy', $activity) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta actividad?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Eliminar
                                    </button>
                                </form>
                            @else
                                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
                                    <p class="text-sm">Solo puedes editar o eliminar actividades del día actual.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
