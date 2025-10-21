<div class="modal fade" id="modalHorario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Gestionar horario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formHorario">
            <input type="hidden" id="asId" />
            <div class="mb-3">
              <label class="form-label">Título</label>
              <input type="text" class="form-control" id="title" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Duración (HH:MM)</label>
              <input type="text" class="form-control" id="duration" placeholder="01:30" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Empleado</label>
              <select class="form-select" id="employeeID" required>
                <option value="">Seleccione un empleado</option>
              </select>
            </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="btn-delete">Eliminar</button>
        <button type="button" class="btn btn-primary" id="btn-save">Guardar</button>
      </div>
    </div>
  </div>
</div>
