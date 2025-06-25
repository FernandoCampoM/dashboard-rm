<?php
// Incluir archivo de configuración
require_once 'config.php';

// Inicializar variables
$searchType = isset($_GET['type']) ? $_GET['type'] : 'code';
$searchValue = isset($_GET['search']) ? $_GET['search'] : '';
$productInfo = null;
$specialPrice = null;
$error = null;
$notFound = false;

// Procesar búsqueda si se proporciona un valor
if (!empty($searchValue)) {
    try {
        if ($searchType === 'barcode') {
            // Buscar por código de barras
            $barcodeResult = callAPI('InfoBarCode', ['barcode' => $searchValue]);
            
            if ($barcodeResult === 'NoMatch') {
                $notFound = true;
            } else {
                // Obtener información del producto usando el código obtenido
                $productInfo = callAPI('ProductInfo', ['Referencia' => $barcodeResult['ProductCode']]);
            }
        } else {
            // Buscar directamente por código de producto
            $productInfo = callAPI('ProductInfo', ['Referencia' => $searchValue]);
            
            if (empty($productInfo)) {
                $notFound = true;
            }
        }
        
        // Si encontramos el producto, verificar si tiene precio especial
        if ($productInfo && !empty($productInfo)) {
            $specialPrice = callAPI('Especial', ['Referencia' => $productInfo['ProductCode'] ?? $searchValue]);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
        }
        .special-price {
            color: #dc3545;
            font-weight: bold;
        }
        .original-price {
            text-decoration: line-through;
            color: #6c757d;
        }
        .low-stock {
            color: #dc3545;
            font-weight: bold;
        }
        .product-detail {
            margin-bottom: 8px;
        }
        .product-detail-label {
            font-weight: bold;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Búsqueda de Productos</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="col-form-label">Buscar por:</label>
                        </div>
                        <div class="col-auto">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="searchTypeCode" value="code" <?php echo ($searchType === 'code') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="searchTypeCode">Código de Producto</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="searchTypeBarcode" value="barcode" <?php echo ($searchType === 'barcode') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="searchTypeBarcode">Código de Barras</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($searchValue); ?>" placeholder="Ingrese código de producto o barras">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <h4 class="alert-heading">Error</h4>
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($notFound): ?>
        <div class="alert alert-warning">
            <h4 class="alert-heading">Producto no encontrado</h4>
            <p>No se encontró ningún producto con el <?php echo ($searchType === 'code') ? 'código' : 'código de barras'; ?> especificado.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($productInfo && !empty($productInfo)): ?>
        <div class="card">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($productInfo['Description']); ?></h2>
                <p class="text-muted mb-0">Código: <?php echo htmlspecialchars($productInfo['ProductCode'] ?? $searchValue); ?></p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="/placeholder.svg?height=200&width=200" alt="Imagen del producto" class="product-image mb-3">
                        
                        <div class="pricing mb-3">
                            <?php if ($specialPrice && !empty($specialPrice)): ?>
                                <div class="original-price"><?php echo formatCurrency($productInfo['Price']); ?></div>
                                <div class="special-price"><?php echo formatCurrency($specialPrice['SpecialPrice']); ?></div>
                                <div class="badge bg-danger">Oferta Especial</div>
                                <div class="small text-muted">
                                    Válido hasta: <?php echo date('d/m/Y', strtotime($specialPrice['DateUntil'])); ?>
                                </div>
                            <?php else: ?>
                                <div class="h3"><?php echo formatCurrency($productInfo['Price']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="stock-info">
                            <div class="<?php echo ($productInfo['OnHand'] < 10) ? 'low-stock' : ''; ?>">
                                Stock: <?php echo number_format($productInfo['OnHand']); ?> unidades
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <h4 class="mb-3">Detalles del Producto</h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="product-detail">
                                    <span class="product-detail-label">Departamento:</span>
                                    <?php echo htmlspecialchars($productInfo['Department']); ?>
                                </div>
                                
                                <div class="product-detail">
                                    <span class="product-detail-label">Categoría:</span>
                                    <?php echo htmlspecialchars($productInfo['Category']); ?>
                                </div>
                                
                                <div class="product-detail">
                                    <span class="product-detail-label">Proveedor:</span>
                                    <?php echo htmlspecialchars($productInfo['Suplier']); ?>
                                </div>
                                
                                <div class="product-detail">
                                    <span class="product-detail-label">Ubicación:</span>
                                    <?php echo htmlspecialchars($productInfo['Location']); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="product-detail">
                                    <span class="product-detail-label">Código de Barras:</span>
                                    <?php echo htmlspecialchars($productInfo['Barcode']); ?>
                                </div>
                                
                                <?php if (!empty($productInfo['BCde13'])): ?>
                                <div class="product-detail">
                                    <span class="product-detail-label">EAN-13:</span>
                                    <?php echo htmlspecialchars($productInfo['BCde13']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="product-detail">
                                    <span class="product-detail-label">Tipo:</span>
                                    <?php echo $productInfo['IsFood'] ? 'Alimento' : 'No Alimento'; ?>
                                    <?php echo $productInfo['IsService'] ? ' (Servicio)' : ''; ?>
                                </div>
                                
                                <?php if (!empty($productInfo['BCFranchise'])): ?>
                                <div class="product-detail">
                                    <span class="product-detail-label">Franquicia:</span>
                                    <?php echo htmlspecialchars($productInfo['BCFranchise']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($productInfo['LongDesc'])): ?>
                        <div class="mt-4">
                            <h5>Descripción Detallada</h5>
                            <p><?php echo nl2br(htmlspecialchars($productInfo['LongDesc'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>