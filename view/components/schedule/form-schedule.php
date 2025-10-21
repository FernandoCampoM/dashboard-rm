<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="eventForm">
          <div class="modal-header">
            <h5 class="modal-title">Agregar Evento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Título</label>
              <input type="text" class="form-control" id="eventTitle" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Fecha Inicio</label>
              <input type="datetime-local" class="form-control" id="eventStart" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Fecha Fin</label>
              <input type="datetime-local" class="form-control" id="eventEnd" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Color</label>
              <input type="color" class="form-control form-control-color" id="eventColor" value="#1e90ff">
            </div>
              
            <div class="mb-3">
              <label class="form-label">Empleado</label>
              <select class="form-select" id="employeeID2" required>
                <option value="">Seleccione un empleado</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" id="deleteEventBtn" class="btn btn-danger d-none">Eliminar</button>
          </div>
        </form>
      </div>
    </div>
</div>