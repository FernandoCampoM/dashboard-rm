/**
 * Funcionalidad para la gestión de Clientes y Productos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let clientsTable;
    let productsMaintenanceTable;
    let clientCategories = [];
    let productDepartments = [];
    let productCategories = [];
    let currentUserId = 'admin'; // Esto debería venir de la sesión del usuario
    
    // Inicializar los eventos para las secciones de mantenimiento
    initMaintenanceEvents();
    
    /**
     * Inicializa los eventos para las secciones de mantenimiento
     */
    function initMaintenanceEvents() {
        // Eventos para la sección de clientes
        document.getElementById('clients-link').addEventListener('click', function() {
            loadClientCategories();
            loadClients();
        });
        
        document.getElementById('refreshClients').addEventListener('click', loadClients);
        document.getElementById('addClientBtn').addEventListener('click', showAddClientModal);
        document.getElementById('saveClientBtn').addEventListener('click', saveClient);
        document.getElementById('applyClientFilters').addEventListener('click', loadClients);
        document.getElementById('resetClientFilters').addEventListener('click', resetClientFilters);
        
        // Eventos para la sección de productos
        document.getElementById('products-maintenance-link').addEventListener('click', function() {
            loadProductDepartments();
            loadProductCategories();
            loadProducts();
        });
        
        document.getElementById('refreshProductsMaintenance').addEventListener('click', loadProducts);
        document.getElementById('addProductBtn').addEventListener('click', showAddProductModal);
        document.getElementById('saveProductBtn').addEventListener('click', saveProduct);
        document.getElementById('applyProductFilters').addEventListener('click', loadProducts);
        document.getElementById('resetProductFilters').addEventListener('click', resetProductFilters);
        
        // Evento para cambiar categorías cuando cambia el departamento
        document.getElementById('productDepartment').addEventListener('change', function() {
            updateProductCategoriesByDepartment(this.value);
        });
        
        // Evento para cambiar categorías en filtros cuando cambia el departamento
        document.getElementById('productDepartmentFilter').addEventListener('change', function() {
            updateProductCategoriesFilterByDepartment(this.value);
        });
    }
    
    /**
     * Carga las categorías de clientes
     */
    function loadClientCategories() {
        toggleLoading(true);
        
        fetch('api_proxy.php?endpoint=ClientCategories')
            .then(response => response.json())
            .then(data => {
                if (data && Array.isArray(data)) {
                    clientCategories = data;
                    
                    // Llenar el select de categorías para filtros
                    const filterSelect = document.getElementById('clientCategoryFilter');
                    filterSelect.innerHTML = '<option value="">Todas las categorías</option>';
                    
                    // Llenar el select de categorías para el formulario
                    const formSelect = document.getElementById('clientCategory');
                    formSelect.innerHTML = '<option value="">Seleccionar categoría</option>';
                    
                    data.forEach(category => {
                        const filterOption = document.createElement('option');
                        filterOption.value = category.CategoryID;
                        filterOption.textContent = category.CategoryName;
                        filterSelect.appendChild(filterOption);
                        
                        const formOption = document.createElement('option');
                        formOption.value = category.CategoryID;
                        formOption.textContent = category.CategoryName;
                        formSelect.appendChild(formOption);
                    });
                }
            })
            .catch(error => {
                console.error('Error cargando categorías de clientes:', error);
                showToast('Error', 'No se pudieron cargar las categorías de clientes', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Carga los departamentos de productos
     */
    function loadProductDepartments() {
        toggleLoading(true);
        
        fetch('api_proxy.php?endpoint=InventoryDepartments&Short=yes')
            .then(response => response.json())
            .then(data => {
                if (data && Array.isArray(data)) {
                    productDepartments = data;
                    
                    // Llenar el select de departamentos para filtros
                    const filterSelect = document.getElementById('productDepartmentFilter');
                    filterSelect.innerHTML = '<option value="">Todos los departamentos</option>';
                    
                    // Llenar el select de departamentos para el formulario
                    const formSelect = document.getElementById('productDepartment');
                    formSelect.innerHTML = '<option value="">Seleccionar departamento</option>';
                    
                    data.forEach(department => {
                        const filterOption = document.createElement('option');
                        filterOption.value = department.DepartmentID;
                        filterOption.textContent = department.DepartmentName;
                        filterSelect.appendChild(filterOption);
                        
                        const formOption = document.createElement('option');
                        formOption.value = department.DepartmentID;
                        formOption.textContent = department.DepartmentName;
                        formSelect.appendChild(formOption);
                    });
                }
            })
            .catch(error => {
                console.error('Error cargando departamentos de productos:', error);
                showToast('Error', 'No se pudieron cargar los departamentos de productos', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Carga las categorías de productos
     */
    function loadProductCategories() {
        toggleLoading(true);
        
        fetch('api_proxy.php?endpoint=InventoryCategories&Short=yes')
            .then(response => response.json())
            .then(data => {
                if (data && Array.isArray(data)) {
                    productCategories = data;
                    
                    // Llenar el select de categorías para filtros
                    const filterSelect = document.getElementById('productCategoryFilter');
                    filterSelect.innerHTML = '<option value="">Todas las categorías</option>';
                    
                    // Llenar el select de categorías para el formulario
                    const formSelect = document.getElementById('productCategory');
                    formSelect.innerHTML = '<option value="">Seleccionar categoría</option>';
                    
                    data.forEach(category => {
                        const filterOption = document.createElement('option');
                        filterOption.value = category.CategoryID;
                        filterOption.textContent = category.CategoryName;
                        filterSelect.appendChild(filterOption);
                        
                        const formOption = document.createElement('option');
                        formOption.value = category.CategoryID;
                        formOption.textContent = category.CategoryName;
                        formSelect.appendChild(formOption);
                    });
                }
            })
            .catch(error => {
                console.error('Error cargando categorías de productos:', error);
                showToast('Error', 'No se pudieron cargar las categorías de productos', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Actualiza las categorías de productos basado en el departamento seleccionado
     */
    function updateProductCategoriesByDepartment(departmentId) {
        const categorySelect = document.getElementById('productCategory');
        categorySelect.innerHTML = '<option value="">Seleccionar categoría</option>';
        
        if (!departmentId) {
            // Si no hay departamento seleccionado, mostrar todas las categorías
            productCategories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.CategoryID;
                option.textContent = category.CategoryName;
                categorySelect.appendChild(option);
            });
            return;
        }
        
        // Filtrar categorías por departamento (esto es un ejemplo, ajustar según la API real)
        const filteredCategories = productCategories.filter(
            category => category.Department == departmentId
        );
        
        filteredCategories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.CategoryID;
            option.textContent = category.CategoryName;
            categorySelect.appendChild(option);
        });
    }
    
    /**
     * Actualiza las categorías de productos en el filtro basado en el departamento seleccionado
     */
    function updateProductCategoriesFilterByDepartment(departmentId) {
        const categorySelect = document.getElementById('productCategoryFilter');
        categorySelect.innerHTML = '<option value="">Todas las categorías</option>';
        
        if (!departmentId) {
            // Si no hay departamento seleccionado, mostrar todas las categorías
            productCategories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.CategoryID;
                option.textContent = category.CategoryName;
                categorySelect.appendChild(option);
            });
            return;
        }
        
        // Filtrar categorías por departamento
        const filteredCategories = productCategories.filter(
            category => category.Department == departmentId
        );
        
        filteredCategories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.CategoryID;
            option.textContent = category.CategoryName;
            categorySelect.appendChild(option);
        });
    }
    
    /**
     * Carga la lista de clientes
     */
    function loadClients() {
        toggleLoading(true);
        
        // Obtener valores de filtros
        const nameFilter = document.getElementById('clientNameFilter').value;
        const categoryFilter = document.getElementById('clientCategoryFilter').value;
        const cityFilter = document.getElementById('clientCityFilter').value;
        
        // Construir parámetros de consulta
        let queryParams = '';
        if (nameFilter) queryParams += `&Name=${encodeURIComponent(nameFilter)}`;
        if (categoryFilter) queryParams += `&Category=${encodeURIComponent(categoryFilter)}`;
        if (cityFilter) queryParams += `&City=${encodeURIComponent(cityFilter)}`;
        
        fetch(`api_proxy.php?endpoint=Clients${queryParams}`)
            .then(response => response.json())
            .then(data => {
                if (data && Array.isArray(data)) {
                    // Preparar datos para la tabla
                    const tableData = data.map(client => {
                        // Determinar la categoría
                        const category = clientCategories.find(c => c.CategoryID === client.Category);
                        const categoryName = category ? category.CategoryName : '';
                        
                        // Crear botones de acción
                        const editButton = `<button class="btn btn-sm btn-primary edit-client" data-id="${client.ClientID}"><i class="fas fa-edit"></i></button>`;
                        const deleteButton = `<button class="btn btn-sm btn-danger ms-1 delete-client" data-id="${client.ClientID}"><i class="fas fa-trash"></i></button>`;
                        
                        return [
                            client.ClientID || '',
                            client.ClientName || '',
                            client.LastName || '',
                            client.City || '',
                            client.Phone || '',
                            client.Email || '',
                            categoryName,
                            client.IsActive === 'S' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>',
                            editButton + deleteButton
                        ];
                    });
                    
                    // Definir columnas
                    const columns = [
                        { title: "ID", data: 0 },
                        { title: "Nombre", data: 1 },
                        { title: "Apellido", data: 2 },
                        { title: "Ciudad", data: 3 },
                        { title: "Teléfono", data: 4 },
                        { title: "Email", data: 5 },
                        { title: "Categoría", data: 6 },
                        { title: "Estado", data: 7 },
                        { title: "Acciones", data: 8 }
                    ];
                    
                    // Inicializar o actualizar la tabla
                    if ($.fn.DataTable.isDataTable('#clientsTable')) {
                        clientsTable.clear().rows.add(tableData).draw();
                    } else {
                        clientsTable = createDataTable('clientsTable', tableData, columns);
                        
                        // Añadir eventos a los botones de acción
                        $('#clientsTable').on('click', '.edit-client', function() {
                            const clientId = $(this).data('id');
                            editClient(clientId);
                        });
                        
                        $('#clientsTable').on('click', '.delete-client', function() {
                            const clientId = $(this).data('id');
                            deleteClient(clientId);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error cargando clientes:', error);
                showToast('Error', 'No se pudieron cargar los clientes', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Carga la lista de productos
     */
    function loadProducts() {
        toggleLoading(true);
        
        // Obtener valores de filtros
        const codeFilter = document.getElementById('productCodeFilter').value;
        const nameFilter = document.getElementById('productNameFilter').value;
        const departmentFilter = document.getElementById('productDepartmentFilter').value;
        const categoryFilter = document.getElementById('productCategoryFilter').value;
        
        // Construir parámetros de consulta
        let queryParams = '';
        if (codeFilter) queryParams += `&ItemCode=${encodeURIComponent(codeFilter)}`;
        if (nameFilter) queryParams += `&Description=${encodeURIComponent(nameFilter)}`;
        if (departmentFilter) queryParams += `&Department=${encodeURIComponent(departmentFilter)}`;
        if (categoryFilter) queryParams += `&Category=${encodeURIComponent(categoryFilter)}`;
        
        fetch(`api_proxy.php?endpoint=GetAllProducts${queryParams}`)
            .then(response => response.json())
            .then(data => {
                if (data && Array.isArray(data)) {
                    // Preparar datos para la tabla
                    // Preparar datos para la tabla
const tableData = data.map(product => {
    // Buscar el nombre del departamento
    const department = productDepartments.find(d => d.DepartmentID === product.Department);
    const departmentName = department ? department.DepartmentName : product.Department;
    
    // Buscar el nombre de la categoría
    const category = productCategories.find(c => c.CategoryID === product.Category);
    const categoryName = category ? category.CategoryName : product.Category;
    
    // Crear botones de acción
    const editButton = `<button class="btn btn-sm btn-primary edit-product" data-id="${product.ProductCode}"><i class="fas fa-edit"></i></button>`;
    const deleteButton = `<button class="btn btn-sm btn-danger ms-1 delete-product" data-id="${product.ProductCode}"><i class="fas fa-trash"></i></button>`;
    
    return [
        product.ProductCode || '',
        product.ProductName || '',
        product.BarCode || '',
        formatCurrency(product.Price || 0),
        formatCurrency(product.Cost || 0),
        safeToLocaleString(product.CurrentStock),
        departmentName, // Mostrar el nombre del departamento en lugar del ID
        categoryName,   // Mostrar el nombre de la categoría en lugar del ID
        //editButton + deleteButton
    ];
});
                    /* const tableData = data.map(product => {
                        // Crear botones de acción
                        const editButton = `<button class="btn btn-sm btn-primary edit-product" data-id="${product.ProductCode}"><i class="fas fa-edit"></i></button>`;
                        const deleteButton = `<button class="btn btn-sm btn-danger ms-1 delete-product" data-id="${product.ProductCode}"><i class="fas fa-trash"></i></button>`;
                        
                        return [
                            product.ProductCode || '',
                            product.ProductName || '',
                            product.BarCode || '',
                            formatCurrency(product.Price || 0),
                            formatCurrency(product.Cost || 0),
                            safeToLocaleString(product.CurrentStock),
                            product.Department || '',
                            product.Category || '',
                            product.Active === '1' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>',
                            editButton + deleteButton
                        ];
                    }); */
                    
                    // Definir columnas
                    const columns = [
                        { title: "Código", data: 0 },
                        { title: "Descripción", data: 1 },
                        { title: "Código de Barras", data: 2 },
                        { title: "Precio", data: 3 },
                        { title: "Costo", data: 4 },
                        { title: "Stock", data: 5 },
                        { title: "Departamento", data: 6 },
                        { title: "Categoría", data: 7 },
                        { title: "Acciones", data: 8 }
                    ];
                    
                    // Inicializar o actualizar la tabla
                    if ($.fn.DataTable.isDataTable('#productsMaintenanceTable')) {
                        console.log("DataTable initialized/updated successfully")
                        productsMaintenanceTable.clear().rows.add(tableData).draw();
                    } else {
                        productsMaintenanceTable = createDataTable('productsMaintenanceTable', tableData, columns);
                        
                        // Añadir eventos a los botones de acción
                        $('#productsMaintenanceTable').on('click', '.edit-product', function() {
                            const productCode = $(this).data('id');
                            editProduct(productCode);
                        });
                        
                        $('#productsMaintenanceTable').on('click', '.delete-product', function() {
                            const productCode = $(this).data('id');
                            deleteProduct(productCode);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error cargando productos:', error);
                showToast('Error', 'No se pudieron cargar los productos', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Muestra el modal para añadir un nuevo cliente
     */
    function showAddClientModal() {
        // Limpiar el formulario
        document.getElementById('clientForm').reset();
        document.getElementById('clientId').value = '';
        
        // Cambiar el título del modal
        document.getElementById('clientModalLabel').textContent = 'Añadir Cliente';
        
        // Mostrar el modal
        const clientModal = new bootstrap.Modal(document.getElementById('clientModal'));
        clientModal.show();
    }
    
    /**
     * Edita un cliente existente
     */
    function editClient(clientId) {
        toggleLoading(true);
        
        fetch(`api_proxy.php?endpoint=Clients&Number=${clientId}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const client = data[0];
                    
                    // Llenar el formulario con los datos del cliente
                    document.getElementById('clientId').value = client.ClientID;
                    document.getElementById('clientName').value = client.ClientName || '';
                    document.getElementById('clientLastName').value = client.LastName || '';
                    document.getElementById('clientAddress1').value = client.Address1 || '';
                    document.getElementById('clientAddress2').value = client.Address2 || '';
                    document.getElementById('clientCity').value = client.City || '';
                    document.getElementById('clientZipCode').value = client.ZipCode || '';
                    document.getElementById('clientCountry').value = client.Country || 'PR';
                    document.getElementById('clientPhone').value = client.Phone || '';
                    document.getElementById('clientEmail').value = client.Email || '';
                    document.getElementById('clientCategory').value = client.Category || '';
                    document.getElementById('clientActive').value = client.IsActive || 'S';
                    
                    // Cambiar el título del modal
                    document.getElementById('clientModalLabel').textContent = 'Editar Cliente';
                    
                    // Mostrar el modal
                    const clientModal = new bootstrap.Modal(document.getElementById('clientModal'));
                    clientModal.show();
                } else {
                    showToast('Error', 'No se encontró el cliente', 'error');
                }
            })
            .catch(error => {
                console.error('Error cargando cliente:', error);
                showToast('Error', 'No se pudo cargar el cliente', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Guarda un cliente (nuevo o existente)
     */
    function saveClient() {
        // Validar el formulario
        const form = document.getElementById('clientForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        toggleLoading(true);
        
        // Obtener los datos del formulario
        const clientId = document.getElementById('clientId').value;
        const clientData = {
            ClientName: document.getElementById('clientName').value,
            LastName: document.getElementById('clientLastName').value,
            Address1: document.getElementById('clientAddress1').value,
            Address2: document.getElementById('clientAddress2').value,
            City: document.getElementById('clientCity').value,
            ZipCode: document.getElementById('clientZipCode').value,
            Country: document.getElementById('clientCountry').value,
            Phone: document.getElementById('clientPhone').value,
            Email: document.getElementById('clientEmail').value,
            Category: document.getElementById('clientCategory').value,
            IsActive: document.getElementById('clientActive').value,
            UserID: currentUserId
        };
        
        // Determinar si es una creación o actualización
        const endpoint = clientId ? 'UpdateClient' : 'CreateClient';
        if (clientId) {
            clientData.ClientID = clientId;
        }
        
        // Enviar los datos al servidor
        fetch(`api_proxy.php?endpoint=${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(clientData)
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    // Cerrar el modal
                    const clientModal = bootstrap.Modal.getInstance(document.getElementById('clientModal'));
                    clientModal.hide();
                    
                    // Recargar la lista de clientes
                    loadClients();
                    
                    // Mostrar mensaje de éxito
                    showToast('Éxito', clientId ? 'Cliente actualizado correctamente' : 'Cliente creado correctamente', 'success');
                } else {
                    showToast('Error', data.message || 'Error al guardar el cliente', 'error');
                }
            })
            .catch(error => {
                console.error('Error guardando cliente:', error);
                showToast('Error', 'No se pudo guardar el cliente', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Elimina un cliente
     */
    function deleteClient(clientId) {
        if (confirm('¿Está seguro de que desea eliminar este cliente?')) {
            toggleLoading(true);
            
            fetch(`api_proxy.php?endpoint=DeleteClient&ClientID=${clientId}&UserID=${currentUserId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        // Recargar la lista de clientes
                        loadClients();
                        
                        // Mostrar mensaje de éxito
                        showToast('Éxito', 'Cliente eliminado correctamente', 'success');
                    } else {
                        showToast('Error', data.message || 'Error al eliminar el cliente', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error eliminando cliente:', error);
                    showToast('Error', 'No se pudo eliminar el cliente', 'error');
                })
                .finally(() => {
                    toggleLoading(false);
                });
        }
    }
    
    /**
     * Muestra el modal para añadir un nuevo producto
     */
    function showAddProductModal() {
        // Limpiar el formulario
        document.getElementById('productForm').reset();
        
        // Cambiar el título del modal
        document.getElementById('productModalLabel').textContent = 'Añadir Producto';
        
        // Mostrar el modal
        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        productModal.show();
    }
    
    /**
     * Edita un producto existente
     */
    function editProduct(productCode) {
        toggleLoading(true);
        
        fetch(`api_proxy.php?endpoint=ProductInfo&Referencia=${productCode}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // Llenar el formulario con los datos del producto
                    document.getElementById('productCode').value = productCode;
                    document.getElementById('productCode').readOnly = true; // No permitir cambiar el código
                    document.getElementById('productDescription').value = data.Description || '';
                    document.getElementById('productBarcode').value = data.Barcode || '';
                    document.getElementById('productCost').value = data.Cost || '';
                    document.getElementById('productPrice').value = data.Price || '';
                    document.getElementById('productDepartment').value = data.DepartmentNum || '';
                    document.getElementById('productCategory').value = data.CategoryNum || '';
                    document.getElementById('productSupplier').value = data.Supplier || '';
                    document.getElementById('productLocation').value = data.Location || '';
                    document.getElementById('productActive').value = data.Active || '1';
                    document.getElementById('productIsFood').checked = data.IsFood === '1';
                    document.getElementById('productIsWic').checked = data.IsWic === '1';
                    document.getElementById('productIsTouch').checked = data.IsTouch === '1';
                    document.getElementById('productIsSerie').checked = data.IsSerie === '1';
                    
                    // Cambiar el título del modal
                    document.getElementById('productModalLabel').textContent = 'Editar Producto';
                    
                    // Mostrar el modal
                    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
                    productModal.show();
                } else {
                    showToast('Error', 'No se encontró el producto', 'error');
                }
            })
            .catch(error => {
                console.error('Error cargando producto:', error);
                showToast('Error', 'No se pudo cargar el producto', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Guarda un producto (nuevo o existente)
     */
    function saveProduct() {
        // Validar el formulario
        const form = document.getElementById('productForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        toggleLoading(true);
        
        // Obtener los datos del formulario
        const productCode = document.getElementById('productCode').value;
        const isNewProduct = document.getElementById('productCode').readOnly === false;
        
        const productData = {
            ItemCode: productCode,
            Description: document.getElementById('productDescription').value,
            Barcode: document.getElementById('productBarcode').value,
            Cost: document.getElementById('productCost').value,
            Price: document.getElementById('productPrice').value,
            DepartmentNum: document.getElementById('productDepartment').value,
            CategoryNum: document.getElementById('productCategory').value,
            Supplier: document.getElementById('productSupplier').value,
            Location: document.getElementById('productLocation').value,
            ReorderPoint: document.getElementById('productReorderPoint').value,
            ReorderQty: document.getElementById('productReorderQty').value,
            Active: document.getElementById('productActive').value,
            IsFood: document.getElementById('productIsFood').checked ? '1' : '0',
            IsWic: document.getElementById('productIsWic').checked ? '1' : '0',
            IsTouch: document.getElementById('productIsTouch').checked ? '1' : '0',
            IsSerie: document.getElementById('productIsSerie').checked ? '1' : '0',
            UserID: currentUserId
        };
        
        // Determinar el endpoint según si es nuevo o existente
        const endpoint = isNewProduct ? 'ProdCreateNewItem' : 'ProdMultipleChange';
        
        // Enviar los datos al servidor
        fetch(`api_proxy.php?endpoint=${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    // Cerrar el modal
                    const productModal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
                    productModal.hide();
                    
                    // Recargar la lista de productos
                    loadProducts();
                    
                    // Mostrar mensaje de éxito
                    showToast('Éxito', isNewProduct ? 'Producto creado correctamente' : 'Producto actualizado correctamente', 'success');
                } else {
                    showToast('Error', data.message || 'Error al guardar el producto', 'error');
                }
            })
            .catch(error => {
                console.error('Error guardando producto:', error);
                showToast('Error', 'No se pudo guardar el producto', 'error');
            })
            .finally(() => {
                toggleLoading(false);
            });
    }
    
    /**
     * Elimina un producto
     */
    function deleteProduct(productCode) {
        if (confirm('¿Está seguro de que desea eliminar este producto?')) {
            toggleLoading(true);
            
            fetch(`api_proxy.php?endpoint=ProdActiveChange&ItemCode=${productCode}&Active=0&UserID=${currentUserId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        // Recargar la lista de productos
                        loadProducts();
                        
                        // Mostrar mensaje de éxito
                        showToast('Éxito', 'Producto desactivado correctamente', 'success');
                    } else {
                        showToast('Error', data.message || 'Error al desactivar el producto', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error desactivando producto:', error);
                    showToast('Error', 'No se pudo desactivar el producto', 'error');
                })
                .finally(() => {
                    toggleLoading(false);
                });
        }
    }
    
    /**
     * Resetea los filtros de clientes
     */
    function resetClientFilters() {
        document.getElementById('clientNameFilter').value = '';
        document.getElementById('clientCategoryFilter').value = '';
        document.getElementById('clientCityFilter').value = '';
        loadClients();
    }
    
    /**
     * Resetea los filtros de productos
     */
    function resetProductFilters() {
        document.getElementById('productCodeFilter').value = '';
        document.getElementById('productNameFilter').value = '';
        document.getElementById('productDepartmentFilter').value = '';
        document.getElementById('productCategoryFilter').value = '';
        loadProducts();
    }
    
    /**
     * Muestra un mensaje toast
     */
    function showToast(title, message, type = 'info') {
        // Verificar si existe la función de toast en el sistema
        if (typeof showNotification === 'function') {
            showNotification(title, message, type);
            return;
        }
        
        // Implementación básica de toast si no existe
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '5000';
            document.body.appendChild(container);
        }
        
        const toastId = 'toast-' + Date.now();
        const toastHTML = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} text-white">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        document.getElementById('toast-container').insertAdjacentHTML('beforeend', toastHTML);
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();
        
        // Eliminar el toast después de ocultarse
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
});