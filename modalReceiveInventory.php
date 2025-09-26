<!-- Modal Recibir Producto -->
<div class="modal fade" id="recibirProductoModal" tabindex="-1" aria-labelledby="recibirProductoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="recibirProductoLabel">Recibir Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body">
        

        <!-- Información del producto -->
        <div class="card p-4 shadow-sm">
          <h5 class="mb-3" id="nombreProducto">Producto de Electroninca USB 5v</h5>
          <p><strong>Código:</strong> <span id="codigoProducto">012345678910</span></p>
          <p><strong>Barcode:</strong> <span id="barcodeProducto">012345678910</span></p>
          
          <!-- Unidades -->
          <div class="form-group mb-3" id="unidadesProductoGroup">
            <label for="unidadesProductoLabel" class="form-label">Unidades:</label>
            <select id="unidadesProducto" class="form-select">
            </select>
          </div>
          
          <!-- Cantidad grande -->
        <div class="text-center display-3 fw-bold mb-3">
        <input type="number" id="cantidadProducto" class="form-control text-center display-3 fw-bold" value="1" min="1">
        </div>

          
          <!-- Botón Recibir -->
          <button id="btnRecibirInventario" class="btn w-100" style="background-color:#ccff00; font-weight:bold;">
            RECIBIR
          </button>
        </div>
        
      </div>
    </div>
  </div>
</div>
