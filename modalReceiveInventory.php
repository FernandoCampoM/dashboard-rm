<!-- Modal Recibir Producto -->
<div class="modal fade" id="recibirProductoModal" tabindex="-1" aria-labelledby="recibirProductoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="recibirProductoLabel">Recibir Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body">
        <div id="movementContainer">
        <canvas class="mb-0" id="ProdMovementChart"></canvas>
        <div id="monthButtonsContainer" class="d-flex justify-content-around my-3">
        </div>
            <table class="table table-bordered text-center" >
        <thead class="table-secondary">
            <tr>
                <th>Ventas</th>
                <th>Costo</th>
                <th>Profit</th>
                <th>Recibos</th>
                <th>Vendidos</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td id="summary-ventas">$0.00</td>
                <td id="summary-costo">$0.00</td>
                <td id="summary-profit">$0.00</td>
                <td id="summary-recibos">0</td>
                <td id="summary-vendidos">0</td>
            </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="summary-section col-md-6">
        <h4 class="fw-bold">Resumen anual</h4>
        <p class="mb-0">Ventas totales (unidades): <span id="annual-total-sales-units">188</span></p>
        <p class="mb-0">Valor bruto de ventas: <span id="annual-gross-sales-value">$450.64</span></p>
        <p class="mb-0">Costos totales: <span id="annual-total-costs">$145.00</span></p>
        <p class="mb-0">Ganancias totales: <span id="annual-total-profit">$145.00</span></p>
    </div>

    <div class="order-suggestion-section col-md-6">
        <h5 class="fw-bold">Orden Sugerida: <span id="suggested-order-quantity">0000</span></h5>
        <p class="text-end text-danger mb-0" style="font-size: 10px;"><span id="suggested-order-excess-message">*producto en exceso para cubrir 6 meses</span></p>
        <p class="mb-0 fw-bold">Demanda:</p>
        <p class="text-end mb-0" style="font-size: 10px;">*basado en las ventas de los últimos 3 meses</p>
        <ul class="mb-0 list-unstyled">
            <li class="text-end mb-0">Semanal: <span id="demand-weekly">2.5</span></li>
            <li class="text-end mb-0">Mensual: <span id="demand-monthly">10.67</span></li>
        </ul>
        <p class="mb-0">Inventario actual: <span id="current-inventory">0075</span></p>
    </div>
    </div>
        <div id="ProdMovementChartMessage" class="text-center p-2 text-muted"></div>
        </div>
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
