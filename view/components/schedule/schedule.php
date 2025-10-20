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
        
        <link rel="stylesheet" type="text/css" href="css/schedule.css" />  
        
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    </head>
    <body class="bg-light">


        <!-- Contenido principal -->
        <div class="container mt-4">
            <h2 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Gestión de Horarios</h2>

            <!-- Botones de acción -->
            <div class="mb-3">
                <!-- 🔹 Este botón abre el modal -->
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal">
                  <i class="fas fa-plus me-1"></i>Agregar Calendario
                </button>

                <button id="btn-refresh" class="btn btn-sm btn-secondary">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar
                </button>

            </div>

            <!-- Tabla de horarios -->

            <div class="row">
                <div class="col-md-2">
                    <h5>Horarios disponibles</h5>
                    <div id="external-events"></div>
                     <!-- Botón para agregar un nuevo horario -->
                    <button id="btn-add" class="btn btn-sm btn-primary mt-2 w-100">
                      <i class="fas fa-plus"></i> Agregar horario
                    </button>
                </div>

                <div class="col-md-10">
                  <div id="calendar"></div>
                </div>
            </div>
        </div>
        <?php include 'form-schedule.php'; ?>
        <?php include 'form-available-schedule.php'; ?>
        

    </body>
   
    
</html>
