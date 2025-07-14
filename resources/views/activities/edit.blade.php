<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Actividad') }}
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
                    <form method="POST" action="{{ route('activities.update', $activity) }}">
                        @csrf
                        @method('PUT')

                        <!-- Título -->
                        <div class="mb-4">
                            <label for="titulo" class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $activity->titulo) }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   required>
                            @error('titulo')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo -->
                        <div class="mb-4">
                            <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="tipo" id="tipo" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                    required>
                                <option value="">Selecciona un tipo</option>
                                <option value="Quipux" {{ old('tipo', $activity->tipo) == 'Quipux' ? 'selected' : '' }}>Quipux</option>
                                <option value="Mantis" {{ old('tipo', $activity->tipo) == 'Mantis' ? 'selected' : '' }}>Mantis</option>
                                <option value="CTIT" {{ old('tipo', $activity->tipo) == 'CTIT' ? 'selected' : '' }}>CTIT</option>
                                <option value="Correo" {{ old('tipo', $activity->tipo) == 'Correo' ? 'selected' : '' }}>Correo</option>
                                <option value="Otros" {{ old('tipo', $activity->tipo) == 'Otros' ? 'selected' : '' }}>Otros</option>
                            </select>
                            @error('tipo')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Número de Referencia -->
                        <div class="mb-4">
                            <label for="numero_referencia" class="block text-sm font-medium text-gray-700">Número de Referencia</label>
                            <input type="text" name="numero_referencia" id="numero_referencia" value="{{ old('numero_referencia', $activity->numero_referencia) }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('numero_referencia')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tiempo -->
                        <div class="mb-4">
                            <label for="tiempo" class="block text-sm font-medium text-gray-700">Tiempo (horas)</label>
                            <input type="number" name="tiempo" id="tiempo" value="{{ old('tiempo', $activity->tiempo) }}" 
                                   step="0.01" min="0.01" max="999.99"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   required>
                            @error('tiempo')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Actividad -->
                        <div class="mb-4">
                            <label for="fecha_actividad" class="block text-sm font-medium text-gray-700">Fecha de Actividad</label>
                            <input type="date" name="fecha_actividad" id="fecha_actividad" 
                                   value="{{ old('fecha_actividad', $activity->fecha_actividad->format('Y-m-d')) }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                   required>
                            @error('fecha_actividad')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-4">
                            <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="4" 
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('observaciones', $activity->observaciones) }}</textarea>
                            @error('observaciones')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('activities.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-4">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Actualizar Actividad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
