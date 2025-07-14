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
                                @foreach(\App\Enums\ActivityType::cases() as $tipo)
                                    <option value="{{ $tipo->value }}" {{ old('tipo', $activity->tipo) == $tipo->value ? 'selected' : '' }}>
                                        {{ $tipo->value }}
                                    </option>
                                @endforeach
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

                        @if(auth()->user()->isJefe())
                            <!-- Fecha de Actividad (solo para jefes) -->
                            <div class="mb-4">
                                <label for="fecha_actividad" class="block text-sm font-medium text-gray-700">Fecha de Actividad</label>
                                <input type="date" name="fecha_actividad" id="fecha_actividad" 
                                       value="{{ old('fecha_actividad', $activity->fecha_actividad->format('Y-m-d')) }}" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                       required>
                                @if ($errors->has('fecha_actividad'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('fecha_actividad') }}</p>
                                @endif
                            </div>
                        @else
                            <!-- Información sobre la fecha para empleados -->
                            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Fecha:</strong> {{ $activity->fecha_actividad->format('d/m/Y') }} 
                                            (solo puedes editar actividades del día actual)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

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
