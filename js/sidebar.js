// Función para manejar el comportamiento del sidebar
document.addEventListener('DOMContentLoaded', function() {
    // Elementos principales
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');
    const menuToggle = document.getElementById('menu-toggle');
    
    // Crear overlay si no existe
    if (!document.querySelector('.sidebar-overlay')) {
        const overlay = document.createElement('div');
        overlay.classList.add('sidebar-overlay');
        document.body.appendChild(overlay);
        
        // Cerrar sidebar al hacer clic en el overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            this.classList.remove('active');
        });
    }
    
    const overlay = document.querySelector('.sidebar-overlay');
    
    // Verificar si hay una preferencia guardada
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    // Aplicar el estado inicial
    if (sidebarCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
        menuToggle.innerHTML = '<i class="fas fa-expand"></i>';
    }
    
    // Manejar clic en el botón de toggle
    menuToggle.addEventListener('click', function() {
        const isCurrentlyCollapsed = sidebar.classList.contains('collapsed');
        
        // Guardar preferencia
        localStorage.setItem('sidebarCollapsed', !isCurrentlyCollapsed);
        
        // Toggle classes
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        
        // Actualizar icono
        if (sidebar.classList.contains('collapsed')) {
            menuToggle.innerHTML = '<i class="fas fa-expand"></i>';
        } else {
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
        
        // Comportamiento específico para móviles
        if (window.innerWidth < 768) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        // Redimensionar gráficos y tablas después de que termine la transición
        setTimeout(function() {
            // Redimensionar todos los gráficos
            if (typeof charts !== 'undefined') {
                Object.values(charts).forEach(function(chart) {
                    if (chart && typeof chart.update === 'function') {
                        chart.update();
                    }
                });
            }
            
            // Ajustar tablas DataTables
            if (typeof tables !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                Object.values(tables).forEach(function(table) {
                    if (table && table.columns && typeof table.columns.adjust === 'function') {
                        table.columns.adjust().draw(false);
                    }
                });
            }
        }, 300);
    });
    
    // Cerrar sidebar en móviles al hacer clic en un enlace
    document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    });
    
    // Ajustar para cambios de tamaño de ventana
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth < 768) {
                if (!sidebar.classList.contains('active')) {
                    overlay.classList.remove('active');
                }
            } else {
                overlay.classList.remove('active');
            }
        }, 250);
    });
});