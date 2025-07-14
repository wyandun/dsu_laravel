import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Manejar errores CSRF automáticamente
document.addEventListener('DOMContentLoaded', function() {
    // Interceptar todos los formularios para manejar errores CSRF
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Refrescar el token CSRF si es necesario
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const csrfInput = form.querySelector('input[name="_token"]');
            
            if (csrfToken && csrfInput) {
                csrfInput.value = csrfToken.getAttribute('content');
            }
        });
    });

    // Mostrar mensaje amigable cuando la página expira
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('page_was_reloaded', 'true');
    });

    if (sessionStorage.getItem('page_was_reloaded')) {
        sessionStorage.removeItem('page_was_reloaded');
        // Si hay un mensaje de error de CSRF, mostrarlo de forma amigable
        const errorMessages = document.querySelectorAll('.text-red-600, .text-red-700');
        errorMessages.forEach(msg => {
            if (msg.textContent.includes('expired') || msg.textContent.includes('token')) {
                msg.textContent = 'La sesión ha expirado. Por favor, intenta nuevamente.';
            }
        });
    }
});
