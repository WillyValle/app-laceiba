// Theme Switcher para APP La Ceiba
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el tema guardado o establecer por defecto 'light'
    const savedTheme = localStorage.getItem('app-theme') || 'light';
    const body = document.body;
    const navbar = document.querySelector('.main-header.navbar');
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    const themeIcon = document.getElementById('theme-icon');

    // Aplicar el tema guardado al cargar la página
    applyTheme(savedTheme);

    // Event listener para el botón de cambio de tema
    themeToggleBtn.addEventListener('click', function() {
        const currentTheme = body.classList.contains('theme-dark') ? 'dark' : 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        applyTheme(newTheme);
        localStorage.setItem('app-theme', newTheme);
    });

    function applyTheme(theme) {
        // Remover todas las clases de tema
        body.classList.remove('theme-light', 'theme-dark');
        
        if (theme === 'dark') {
            body.classList.add('theme-dark');
            navbar.classList.remove('navbar-white', 'navbar-light');
            navbar.classList.add('navbar-dark');
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
            themeToggleBtn.title = 'Cambiar a tema claro';
        } else {
            body.classList.add('theme-light');
            navbar.classList.remove('navbar-dark');
            navbar.classList.add('navbar-white', 'navbar-light');
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
            themeToggleBtn.title = 'Cambiar a tema oscuro';
        }
    }

    // Función para detectar preferencias del sistema (opcional)
    function detectSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    // Listener para cambios en las preferencias del sistema (opcional)
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem('app-theme')) {
                applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }
});