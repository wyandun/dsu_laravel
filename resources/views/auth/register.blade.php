<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Rol')" />
            <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">Selecciona un rol</option>
                <option value="empleado" {{ old('role') == 'empleado' ? 'selected' : '' }}>Empleado</option>
                <option value="jefe" {{ old('role') == 'jefe' ? 'selected' : '' }}>Jefe</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Tipo de Jefe (solo si es jefe) -->
        <div class="mt-4" id="tipo_jefe_container" style="display: none;">
            <x-input-label for="tipo_jefe" :value="__('Tipo de Jefe')" />
            <select id="tipo_jefe" name="tipo_jefe" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Selecciona el tipo</option>
                <option value="coordinador" {{ old('tipo_jefe') == 'coordinador' ? 'selected' : '' }}>Coordinador</option>
                <option value="director" {{ old('tipo_jefe') == 'director' ? 'selected' : '' }}>Director</option>
            </select>
            <x-input-error :messages="$errors->get('tipo_jefe')" class="mt-2" />
        </div>

        <!-- Coordinación -->
        <div class="mt-4">
            <x-input-label for="coordinacion" :value="__('Coordinación')" />
            <select id="coordinacion" name="coordinacion" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Selecciona una coordinación</option>
                <option value="Coordinación de TICS" {{ old('coordinacion') == 'Coordinación de TICS' ? 'selected' : '' }}>Coordinación de TICS</option>
            </select>
            <x-input-error :messages="$errors->get('coordinacion')" class="mt-2" />
        </div>

        <!-- Dirección -->
        <div class="mt-4">
            <x-input-label for="direccion" :value="__('Dirección')" />
            <select id="direccion" name="direccion" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Selecciona una dirección</option>
                <option value="Dirección de Seguridad" {{ old('direccion') == 'Dirección de Seguridad' ? 'selected' : '' }}>Dirección de Seguridad</option>
                <option value="Dirección de Infraestructura" {{ old('direccion') == 'Dirección de Infraestructura' ? 'selected' : '' }}>Dirección de Infraestructura</option>
                <option value="Dirección de Desarrollo de Soluciones" {{ old('direccion') == 'Dirección de Desarrollo de Soluciones' ? 'selected' : '' }}>Dirección de Desarrollo de Soluciones</option>
                <option value="Dirección de Gestión de Servicios Informáticos" {{ old('direccion') == 'Dirección de Gestión de Servicios Informáticos' ? 'selected' : '' }}>Dirección de Gestión de Servicios Informáticos</option>
            </select>
            <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
            <p class="text-sm text-gray-600 mt-1">Para coordinadores, dejar vacío para supervisar todas las direcciones.</p>
        </div>

        <script>
            document.getElementById('role').addEventListener('change', function() {
                const tipoJefeContainer = document.getElementById('tipo_jefe_container');
                const tipoJefeSelect = document.getElementById('tipo_jefe');
                
                if (this.value === 'jefe') {
                    tipoJefeContainer.style.display = 'block';
                    tipoJefeSelect.required = true;
                } else {
                    tipoJefeContainer.style.display = 'none';
                    tipoJefeSelect.required = false;
                    tipoJefeSelect.value = '';
                }
            });

            // Trigger on page load if jefe is already selected
            if (document.getElementById('role').value === 'jefe') {
                document.getElementById('tipo_jefe_container').style.display = 'block';
                document.getElementById('tipo_jefe').required = true;
            }
        </script>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
