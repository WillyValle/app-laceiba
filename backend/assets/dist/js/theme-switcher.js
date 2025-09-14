// Theme Switcher combinado para AdminLTE
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('app-theme') || 'light';
    const body = document.body;
    const navbar = document.querySelector('.main-header.navbar');
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    const themeIcon = document.getElementById('theme-icon');

    // Aplicar tema al cargar
    applyTheme(savedTheme);

    // Al hacer clic en el botón
    themeToggleBtn.addEventListener('click', function() {
        const currentTheme = body.classList.contains('dark-mode') ? 'dark' : 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        applyTheme(newTheme);
        localStorage.setItem('app-theme', newTheme);
    });

    function applyTheme(theme) {
        if (theme === 'dark') {
            body.classList.remove('theme-light');
            body.classList.add('dark-mode');

            navbar.classList.remove('navbar-white', 'navbar-light');
            navbar.classList.add('navbar-dark');

            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
            themeToggleBtn.title = 'Cambiar a tema claro';
        } else {
            body.classList.remove('dark-mode');
            body.classList.add('theme-light');

            navbar.classList.remove('navbar-dark');
            navbar.classList.add('navbar-white', 'navbar-light');

            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
            themeToggleBtn.title = 'Cambiar a tema oscuro';
        }
    }
});
