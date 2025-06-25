// Función para manejar el comportamiento del sidebar y ajustar el contenido
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
        
        // Cerrar sidebar al hacer clic en el overlay (solo móvil)
        overlay.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('active');
                this.classList.remove('active');
            }
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
        menuToggle.setAttribute('title', 'Expandir menú');
    }
    
    // Manejar clic en el botón de toggle
    menuToggle.addEventListener('click', function() {
        const isMobile = window.innerWidth < 768;
        const isCurrentlyCollapsed = sidebar.classList.contains('collapsed');
        
        // Guardar preferencia
        localStorage.setItem('sidebarCollapsed', !isCurrentlyCollapsed);
        
        // Toggle de clases
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        
        // Actualizar icono
        if (sidebar.classList.contains('collapsed')) {
            menuToggle.innerHTML = '<i class="fas fa-expand"></i>';
            menuToggle.setAttribute('title', 'Expandir menú');
        } else {
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            menuToggle.setAttribute('title', 'Contraer menú');
        }
        
        // Comportamiento móvil
        if (isMobile) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        // Redimensionar gráficos y tablas después de la transición
        setTimeout(function() {
            // Redimensionar todos los gráficos
            if (typeof charts !== 'undefined') {
                Object.values(charts).forEach(chart => {
                    if (chart) {
                        try {
                            // Para Chart.js
                            chart.update();
                            
                            // Actualizar dimensiones del canvas si es necesario
                            const canvas = chart.canvas;
                            if (canvas) {
                                const parent = canvas.parentNode;
                                if (parent) {
                                    canvas.width = parent.clientWidth;
                                    canvas.height = parent.clientHeight;
                                    chart.resize();
                                }
                            }
                        } catch (e) {
                            console.warn('Error al actualizar gráfico:', e);
                        }
                    }
                });
            }
            
            // Ajustar tablas DataTables
            if (typeof tables !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                Object.values(tables).forEach(table => {
                    if (table && table.columns && typeof table.columns.adjust === 'function') {
                        try {
                            table.columns.adjust().draw(false);
                        } catch (e) {
                            console.warn('Error al ajustar tabla:', e);
                        }
                    }
                });
            }
        }, 350); // Dar tiempo para que termine la transición CSS
    });
    
    // Cerrar sidebar en móviles al hacer clic en enlaces
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    });
    
    // Manejar cambios de tamaño de ventana
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Comportamiento en móviles
            if (window.innerWidth < 768) {
                if (!sidebar.classList.contains('active')) {
                    overlay.classList.remove('active');
                }
            } else {
                overlay.classList.remove('active');
            }
            
            // Redimensionar gráficos
            if (typeof charts !== 'undefined') {
                Object.values(charts).forEach(chart => {
                    if (chart) {
                        try {
                            chart.update();
                        } catch (e) {
                            console.warn('Error al actualizar gráfico en resize:', e);
                        }
                    }
                });
            }
            
            // Ajustar tablas
            if (typeof tables !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                Object.values(tables).forEach(table => {
                    if (table && table.columns && typeof table.columns.adjust === 'function') {
                        try {
                            table.columns.adjust().draw(false);
                        } catch (e) {
                            console.warn('Error al ajustar tabla en resize:', e);
                        }
                    }
                });
            }
        }, 250);
    });
});