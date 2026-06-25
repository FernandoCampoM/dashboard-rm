<!-- Contenido principal -->
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Gestión de Horarios</h2>

    <div class="mb-3">
        <button id="btn-refresh" class="btn btn-sm btn-secondary">
            <i class="fas fa-sync-alt me-1"></i>Actualizar
        </button>
    </div>

    <div class="row">
        <div class="col-md-2">
            <h5>Horarios disponibles</h5>
            <div id="external-events"></div>
            <button id="btn-add" class="btn btn-sm btn-primary mt-2 w-100">
              <i class="fas fa-plus"></i> Agregar horario
            </button>
        </div>
        <div class="col-md-10">
          <div id="calendar"></div>
        </div>
    </div>
</div>
