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
        handleSidebarToggleResize();
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
            handleSidebarToggleResize();
        }, 250);
    });
    
    // Ejecutar el redimensionamiento inicial
    handleSidebarToggleResize();
});

// Función específica para redimensionar gráficos y tablas
function handleSidebarToggleResize() {
    console.log("Redimensionando componentes después de toggle del sidebar");
    
    // Usar setTimeout para dar tiempo a que termine la transición CSS
    setTimeout(() => {
        // Redimensionar todos los gráficos
        if (typeof charts !== 'undefined') {
            Object.keys(charts).forEach(chartKey => {
                const chart = charts[chartKey];
                if (chart) {
                    try {
                        // Verificar si es un gráfico de Chart.js
                        if (typeof chart.update === 'function') {
                            // Obtener y ajustar el canvas
                            const canvas = chart.canvas;
                            if (canvas) {
                                // Asegurar que el canvas use el ancho completo disponible
                                canvas.style.width = '100%';
                                canvas.style.height = 'auto';
                                
                                // Llamar a los métodos de redimensionamiento
                                if (typeof chart.resize === 'function') {
                                    chart.resize();
                                }
                                chart.update();
                                
                                console.log(`Gráfico ${chartKey} redimensionado`);
                            }
                        }
                    } catch (e) {
                        console.warn(`Error al redimensionar gráfico:`, e);
                    }
                }
            });
        }
        
        // Ajustar tablas DataTables
        if (typeof tables !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
            Object.keys(tables).forEach(tableKey => {
                const table = tables[tableKey];
                if (table && table.columns && typeof table.columns.adjust === 'function') {
                    try {
                        // Ajustar columnas sin redibujar completamente
                        table.columns.adjust().draw(false);
                        console.log(`Tabla ${tableKey} ajustada`);
                    } catch (e) {
                        console.warn(`Error al ajustar tabla:`, e);
                    }
                }
            });
        }
    }, 350); // Esperar 350ms para que termine la transición CSS
}