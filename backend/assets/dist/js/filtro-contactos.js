// Funcionalidad de filtro para tabla de contactos
document.addEventListener('DOMContentLoaded', function() {
    const filterHeader = document.getElementById('owner-filter-header');
    const filterDropdown = document.getElementById('owner-filter-dropdown');
    const filterOptions = document.querySelectorAll('.filter-option');
    const tableRows = document.querySelectorAll('tbody tr');

    // Verificar si los elementos existen antes de continuar
    if (!filterHeader || !filterDropdown || filterOptions.length === 0) {
        console.warn('Elementos del filtro de contactos no encontrados');
        return;
    }

    // Toggle dropdown cuando se hace click en el header
    filterHeader.addEventListener('click', function(e) {
        e.stopPropagation();
        filterDropdown.classList.toggle('show');
    });

    // Cerrar dropdown cuando se hace click fuera
    document.addEventListener('click', function() {
        filterDropdown.classList.remove('show');
    });

    // Prevenir que el dropdown se cierre cuando se hace click dentro
    filterDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Manejar filtros
    filterOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            
            // Actualizar opción activa
            filterOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            // Aplicar filtro
            applyFilter(filterValue);
            
            // Cerrar dropdown
            filterDropdown.classList.remove('show');
        });
    });

    function applyFilter(filterValue) {
        tableRows.forEach(function(row) {
            const ownerType = row.getAttribute('data-owner-type');
            
            if (filterValue === 'all') {
                row.style.display = '';
            } else if (ownerType === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Actualizar contador de filas visibles (opcional)
        updateRowCount();
    }

    function updateRowCount() {
        const visibleRows = document.querySelectorAll('tbody tr:not([style*="display: none"])').length;
        console.log(`Mostrando ${visibleRows} contacto(s)`);
        
        // Opcional: Mostrar mensaje si no hay resultados
        if (visibleRows === 0) {
            console.log('No se encontraron contactos con el filtro aplicado');
        }
    }
});