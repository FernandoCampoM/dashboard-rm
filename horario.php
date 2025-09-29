<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Gestión de Horario</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- FontAwesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

        <link rel="stylesheet" type="text/css" href="css/horario.css" />

        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        
    </head>
    <body class="bg-light">


        <!-- Contenido principal -->
        <div class="container mt-4">
            <h2 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Gestión de Horarios</h2>

            <!-- Botones de acción -->
            <div class="mb-3">
                <button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Agregar Horario</button>
                <button class="btn btn-sm btn-secondary"><i class="fas fa-sync-alt me-1"></i>Actualizar</button>
            </div>

            <!-- Tabla de horarios -->

            <div id="calendar"></div>


        </div>


    </body>
    
</html>
