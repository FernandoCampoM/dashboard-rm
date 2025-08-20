// js/maintenance.js (versión mejorada)

// Global variables to store product departments and categories
let productDepartments = []
let productCategories = []
let productsMaintenanceTable

// Function to format currency
function formatCurrency(number) {
  return new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" }).format(number)
}

// Function to safely convert to locale string, handling null/undefined
function safeToLocaleString(value) {
  return value != null ? value.toLocaleString() : ""
}

// Function to toggle loading state
function toggleLoading(isLoading) {
  
  const loadingOverlay = document.getElementById("loadingOverlay")
  if (loadingOverlay) {
    loadingOverlay.style.display = isLoading ? "flex" : "none"
  }
}

// Enhanced function to show toast notifications with console logging
function showToast(title, message, type) {
  
  // Si estás usando una biblioteca de toast, implementa esto aquí
  // De lo contrario, puedes usar alert por ahora
  alert(`${title}: ${message}`)
}

// Function to extract data from API response
function extractApiData(data) {
  // Check if data has RESULT property (nested structure)
  if (data && data.RESULT && Array.isArray(data.RESULT)) {
    // If RESULT is an array of arrays, take the first array
    if (Array.isArray(data.RESULT[0])) {
      return data.RESULT[0]
    }
    // If RESULT is just an array of objects, return it directly
    return data.RESULT
  }

  // If data is already an array, return it as is
  if (Array.isArray(data)) {
    return data
  }

  // If we can't determine the structure, return empty array
  console.error("Unknown data structure:", data)
  return []
}

// Debug function to log API responses
function logApiResponse(endpoint, data) {
  
  const extractedData = extractApiData(data)
  
  if (!extractedData || extractedData.length === 0) {
    console.warn(`Warning: Empty data extracted from ${endpoint}`)
  }
  return extractedData
}

// Function to load products from the API with enhanced debugging
function loadProducts() {
  
  
  

  // Always reload departments and categories to ensure we have the latest data
  loadProductDepartments(() => {
    loadProductCategories(() => {
      loadProductsData()
    })
  })
}

// Function that actually loads the product data
function loadProductsData() {
  
  toggleLoading(true)

  // Obtener valores de filtros
  const codeFilter = document.getElementById("productCodeFilter")
    ? document.getElementById("productCodeFilter").value
    : ""
  const nameFilter = document.getElementById("productNameFilter")
    ? document.getElementById("productNameFilter").value
    : ""
  const departmentFilter = document.getElementById("productDepartmentFilter")
    ? document.getElementById("productDepartmentFilter").value
    : ""
  const categoryFilter = document.getElementById("productCategoryFilter")
    ? document.getElementById("productCategoryFilter").value
    : ""

  console.log("Filter values:", {
    code: codeFilter,
    name: nameFilter,
    department: departmentFilter,
    category: categoryFilter,
  })

  // Construir parámetros de consulta
  let queryParams = ""
  if (codeFilter) queryParams += `&ItemCode=${encodeURIComponent(codeFilter)}`
  if (nameFilter) queryParams += `&Description=${encodeURIComponent(nameFilter)}`
  if (departmentFilter) queryParams += `&Department=${encodeURIComponent(departmentFilter)}`
  if (categoryFilter) queryParams += `&Category=${encodeURIComponent(categoryFilter)}`

  const apiUrl = `api_proxy.php?endpoint=GetAllProducts${queryParams}`
  

  // Use a timeout to prevent hanging requests
  const timeoutId = setTimeout(() => {
    toggleLoading(false)
    showToast("Error", "La solicitud ha tardado demasiado tiempo. Por favor, inténtelo de nuevo.", "error")
  }, 30000) // 30 seconds timeout

  fetch(apiUrl)
    .then((response) => {
      clearTimeout(timeoutId) // Clear the timeout
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      const products = logApiResponse("GetAllProducts", data)

      if (products && products.length > 0) {
        

        // Preparar datos para la tabla
        const tableData = products.map((product) => {
          // Log each product for debugging
          

          // Buscar el nombre del departamento
          const department = productDepartments.find((d) => d.DepartmentID === product.Department)
          
          const departmentName = department ? department.DepartmentName : product.Department

          // Buscar el nombre de la categoría
          const category = productCategories.find((c) => c.CategoryID === product.Category)
          
          const categoryName = category ? category.CategoryName : product.Category

          // Crear botones de acción
          const editButton = `<button class="btn btn-sm btn-primary edit-product" data-id="${product.ProductCode}"><i class="fas fa-edit"></i></button>`
          const deleteButton = `<button class="btn btn-sm btn-danger ms-1 delete-product" data-id="${product.ProductCode}"><i class="fas fa-trash"></i></button>`

          return [
            product.ProductCode || "",
            product.ProductName || "",
            product.BarCode || "",
            formatCurrency(product.Price || 0),
            formatCurrency(product.Cost || 0),
            safeToLocaleString(product.CurrentStock),
            departmentName, // Mostrar el nombre del departamento en lugar del ID
            categoryName, // Mostrar el nombre de la categoría en lugar del ID
            product.Active === "1"
              ? '<span class="badge bg-success">Activo</span>'
              : '<span class="badge bg-danger">Inactivo</span>',
            editButton + deleteButton,
          ]
        })

        

        try {
          // Check if table element exists
          const tableElement = document.getElementById("productsMaintenanceTable")
          if (!tableElement) {
            console.error("Table element not found")
            showToast("Error", "No se encontró la tabla de productos", "error")
            return
          }

          

          // Check if jQuery is available
          if (typeof jQuery === "undefined") {
            console.error("jQuery is not loaded!")
            showToast("Error", "jQuery no está cargado. Verifique las dependencias.", "error")
            return
          }

          // Use jQuery safely
          const $ = jQuery

          // Check if DataTables is available
          if (!$.fn.DataTable) {
            console.error("DataTables is not loaded!")
            showToast("Error", "DataTables no está cargado. Verifique las dependencias.", "error")
            return
          }

          // Define columns with proper width and alignment
          const columns = [
            { title: "Código", data: 0, width: "10%" },
            { title: "Descripción", data: 1, width: "15%" },
            { title: "Código de Barras", data: 2, width: "10%" },
            { title: "Precio", data: 3, width: "8%", className: "text-end" },
            { title: "Costo", data: 4, width: "8%", className: "text-end" },
            { title: "Stock", data: 5, width: "7%", className: "text-center" },
            { title: "Departamento", data: 6, width: "12%" },
            { title: "Categoría", data: 7, width: "12%" },
            { title: "Estado", data: 8, width: "8%", className: "text-center" },
            { title: "Acciones", data: 9, width: "10%", className: "text-center", orderable: false },
          ]

          // Inicializar o actualizar la tabla
          if ($.fn.DataTable.isDataTable("#productsMaintenanceTable")) {
            
            $("#productsMaintenanceTable").DataTable().clear().rows.add(tableData).draw()
          } else {
            
            productsMaintenanceTable = $("#productsMaintenanceTable").DataTable({
              data: tableData,
              columns: columns,
              language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-AR.json",
              },
              responsive: true,
              autoWidth: false, // Disable auto width calculation
              columnDefs: [
                { responsivePriority: 1, targets: [0, 1, 9] }, // These columns are most important
                { responsivePriority: 2, targets: [3, 8] }, // These columns are next important
              ],
              dom:
                '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
              initComplete: function () {
                // Add some custom styling after table is initialized
                $(this).closest(".dataTables_wrapper").addClass("card-body p-0")
              },
            })

            // Añadir eventos a los botones de acción - only add once
            $(document).off("click", ".edit-product").on("click", ".edit-product", function () {
              
              const productCode = $(this).data("id")
              
              editProduct(productCode)
            })

            $(document).off("click", ".delete-product").on("click", ".delete-product", function () {
              
              const productCode = $(this).data("id")
              
              deleteProduct(productCode)
            })
          }

          
        } catch (error) {
          console.error("Error initializing DataTable:", error)
          showToast("Error", "Error al inicializar la tabla de productos: " + error.message, "error")
        }
      } else {
        console.error("No products found in data:", data)
        showToast("Información", "No se encontraron productos con los filtros seleccionados", "info")
      }
    })
    .catch((error) => {
      clearTimeout(timeoutId) // Clear the timeout
      console.error("Error cargando productos:", error)
      showToast("Error", "No se pudieron cargar los productos: " + error.message, "error")
    })
    .finally(() => {
      toggleLoading(false)
    })
}

// Function to load product departments
function loadProductDepartments(callback) {
  
  toggleLoading(true)

  // Use a timeout to prevent hanging requests
  const timeoutId = setTimeout(() => {
    toggleLoading(false)
    showToast("Error", "La solicitud de departamentos ha tardado demasiado tiempo.", "error")
  }, 30000) // 30 seconds timeout

  const apiUrl = "api_proxy.php?endpoint=InventoryDepartments&Short=yes"
  

  fetch(apiUrl)
    .then((response) => {
      clearTimeout(timeoutId) // Clear the timeout
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      const departments = logApiResponse("InventoryDepartments", data)

      if (departments && departments.length > 0) {
        productDepartments = departments
        

        // Llenar el select de departamentos para filtros
        const filterSelect = document.getElementById("productDepartmentFilter")
        if (filterSelect) {
          filterSelect.innerHTML = '<option value="">Todos los departamentos</option>'

          departments.forEach((department) => {
            
            const filterOption = document.createElement("option")
            filterOption.value = department.DepartmentID
            filterOption.textContent = department.DepartmentName
            filterSelect.appendChild(filterOption)
          })
        } else {
          console.warn("Department filter select not found")
        }

        // Llenar el select de departamentos para el formulario
        const formSelect = document.getElementById("productDepartment")
        if (formSelect) {
          formSelect.innerHTML = '<option value="">Seleccionar departamento</option>'

          departments.forEach((department) => {
            const formOption = document.createElement("option")
            formOption.value = department.DepartmentID
            formOption.textContent = department.DepartmentName
            formSelect.appendChild(formOption)
          })
        }

        // Call the callback function if provided
        if (typeof callback === "function") {
          callback()
        }
      } else {
        console.error("No departments found in data:", data)
        showToast("Error", "No se encontraron departamentos", "error")

        // Call the callback function even on error to continue the flow
        if (typeof callback === "function") {
          callback()
        }
      }
    })
    .catch((error) => {
      clearTimeout(timeoutId) // Clear the timeout
      console.error("Error cargando departamentos de productos:", error)
      showToast("Error", "No se pudieron cargar los departamentos: " + error.message, "error")

      // Call the callback function even on error to continue the flow
      if (typeof callback === "function") {
        callback()
      }
    })
    .finally(() => {
      toggleLoading(false)
    })
}

// Function to load product categories
function loadProductCategories(callback) {
  
  toggleLoading(true)

  // Use a timeout to prevent hanging requests
  const timeoutId = setTimeout(() => {
    toggleLoading(false)
    showToast("Error", "La solicitud de categorías ha tardado demasiado tiempo.", "error")
  }, 30000) // 30 seconds timeout

  const apiUrl = "api_proxy.php?endpoint=InventoryCategories&Short=yes"
  

  fetch(apiUrl)
    .then((response) => {
      clearTimeout(timeoutId) // Clear the timeout
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      console.log("Antes del error")
      const categories = logApiResponse("InventoryCategories", data)

      if (categories && categories.length > 0) {
        productCategories = categories
        

        // Llenar el select de categorías para filtros
        const filterSelect = document.getElementById("productCategoryFilter")
        if (filterSelect) {
          filterSelect.innerHTML = '<option value="">Todas las categorías</option>'

          categories.forEach((category) => {
            
            const filterOption = document.createElement("option")
            filterOption.value = category.CategoryID
            filterOption.textContent = category.CategoryName
            filterSelect.appendChild(filterOption)
          })
        } else {
          console.warn("Category filter select not found")
        }

        // Llenar el select de categorías para el formulario
        const formSelect = document.getElementById("productCategory")
        if (formSelect) {
          formSelect.innerHTML = '<option value="">Seleccionar categoría</option>'

          categories.forEach((category) => {
            const formOption = document.createElement("option")
            formOption.value = category.CategoryID
            formOption.textContent = category.CategoryName
            formSelect.appendChild(formOption)
          })
        }

        // Call the callback function if provided
        if (typeof callback === "function") {
          callback()
        }
      } else {
        console.error("No categories found in data:", data)
        showToast("Error", "No se encontraron categorías", "error")

        // Call the callback function even on error to continue the flow
        if (typeof callback === "function") {
          callback()
        }
      }
    })
    .catch((error) => {
      clearTimeout(timeoutId) // Clear the timeout
      console.error("Error cargando categorías de productos:", error)
      showToast("Error", "No se pudieron cargar las categorías: " + error.message, "error")

      // Call the callback function even on error to continue the flow
      if (typeof callback === "function") {
        callback()
      }
    })
    .finally(() => {
      toggleLoading(false)
    })
}

// Function to edit a product
function editProduct(productCode) {
  

  // Validate product code
  if (!productCode) {
    console.error("No product code provided")
    showToast("Error", "No se proporcionó un código de producto válido", "error")
    return
  }

  // Show loading indicator
  toggleLoading(true)

  // First, try to find the product in the existing table data
  
  let foundProduct = null

  // Check if jQuery and DataTables are available
  if (typeof jQuery !== "undefined" && jQuery.fn.DataTable) {
    const table = jQuery("#productsMaintenanceTable").DataTable()
    if (table) {
      // Get all data from the table
      const tableData = table.data().toArray()
      

      // Find the row with matching product code
      const productRow = tableData.find((row) => row[0] === productCode)
      if (productRow) {
        

        // Create a product object from the row data
        foundProduct = {
          ProductCode: productRow[0],
          ProductName: productRow[1],
          BarCode: productRow[2],
          Price: productRow[3].replace(/[^\d.,]/g, ""), // Remove currency symbols
          Cost: productRow[4].replace(/[^\d.,]/g, ""),
          CurrentStock: productRow[5],
          Department: productRow[6],
          Category: productRow[7],
          Active: productRow[8].includes("Activo") ? "1" : "0",
        }

        
        populateProductForm(foundProduct)
        return
      } else {
        console.log("Product not found in table data, will try API...")
      }
    }
  }

  // If we couldn't find the product in the table, try the API
  // Try different API endpoint formats
  const apiEndpoints = [
    `api_proxy.php?endpoint=GetProduct&ItemCode=${encodeURIComponent(productCode)}`,
    `api_proxy.php?endpoint=GetProductDetails&ItemCode=${encodeURIComponent(productCode)}`,
    `api_proxy.php?endpoint=GetAllProducts&ItemCode=${encodeURIComponent(productCode)}`,
  ]

  

  // Try each endpoint in sequence
  tryNextEndpoint(0)

  function tryNextEndpoint(index) {
    if (index >= apiEndpoints.length) {
      console.error("All API endpoints failed")
      toggleLoading(false)
      showToast("Error", "No se pudo cargar los detalles del producto después de intentar múltiples endpoints", "error")
      return
    }

    const apiUrl = apiEndpoints[index]
    

    fetch(apiUrl)
      .then((response) => {
        
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
        

        // If data is false or empty, try the next endpoint
        if (!data || (typeof data === "object" && Object.keys(data).length === 0)) {
          
          tryNextEndpoint(index + 1)
          return
        }

        // Try to extract the product from different data structures
        const product = extractProductFromResponse(data, productCode)

        if (product) {
          
          populateProductForm(product)
        } else {
          console.log(`Couldn't extract product from endpoint ${index + 1}, trying next endpoint...`)
          tryNextEndpoint(index + 1)
        }
      })
      .catch((error) => {
        console.error(`Error with endpoint ${index + 1}:`, error)
        console.log("Trying next endpoint...")
        tryNextEndpoint(index + 1)
      })
  }

  // Helper function to extract product from different response structures
  function extractProductFromResponse(data, productCode) {
    // Case 1: data.RESULT is an array of products
    if (data.RESULT && Array.isArray(data.RESULT)) {
      // If it's an array of arrays, take the first array
      if (Array.isArray(data.RESULT[0])) {
        const products = data.RESULT[0]
        return products.find((p) => p.ProductCode === productCode || p.ItemCode === productCode)
      }
      // If RESULT is just an array of objects, search in it
      return data.RESULT.find((p) => p.ProductCode === productCode || p.ItemCode === productCode)
    }

    // Case 2: data is an array of products
    if (Array.isArray(data)) {
      return data.find((p) => p.ProductCode === productCode || p.ItemCode === productCode)
    }

    // Case 3: data is the product object itself
    if (typeof data === "object" && data !== null) {
      if (data.ProductCode === productCode || data.ItemCode === productCode) {
        return data
      }
    }

    return null
  }

  // Helper function to populate the form with product data
  function populateProductForm(product) {
    // Reset the form
    document.getElementById("productForm").reset()

    

    // Map the API field names to the form field IDs with fallbacks for different field names
    const fieldMappings = {
      // Try different possible API field names
      ProductCode: "productCode",
      ItemCode: "productCode", // Alternative field name

      ProductName: "productName",
      Description: "productName", // Alternative field name

      BarCode: "productBarCode",
      Barcode: "productBarCode", // Alternative field name

      Price: "productPrice",
      UnitPrice: "productPrice", // Alternative field name

      Cost: "productCost",
      UnitCost: "productCost", // Alternative field name

      CurrentStock: "productStock",
      Stock: "productStock", // Alternative field name
      Quantity: "productStock", // Alternative field name

      Department: "productDepartment",
      DepartmentID: "productDepartment", // Alternative field name

      Category: "productCategory",
      CategoryID: "productCategory", // Alternative field name

      Active: "productActive",
      IsActive: "productActive", // Alternative field name
    }

    // Log all available fields for debugging
    

    // Set form values based on mappings
    for (const [apiField, formField] of Object.entries(fieldMappings)) {
      const formElement = document.getElementById(formField)
      if (formElement && product[apiField] !== undefined) {
        if (formField === "productActive") {
          // Handle checkbox
          const activeValue = product[apiField]
          formElement.checked =
            activeValue === "1" ||
            activeValue === 1 ||
            activeValue === true ||
            activeValue === "true" ||
            activeValue === "S"
          
        } else {
          // Handle regular inputs
          formElement.value = product[apiField] || ""
          console.log(`Set ${formField} to "${formElement.value}"`)
        }
      }
    }

    // Show form
    document.getElementById("productFormTitle").textContent = "Editar Producto"
    // Declare jQuery if it's not already declared
    if (typeof jQuery === "undefined") {
      console.error("jQuery is not loaded!")
      showToast("Error", "jQuery no está cargado. Verifique las dependencias.", "error")
      return
    }
    jQuery("#productModal").modal("show")
    toggleLoading(false)
  }
}

// Function to delete a product
function deleteProduct(productCode) {
  

  if (confirm("¿Está seguro que desea eliminar este producto?")) {
    toggleLoading(true)
    
    fetch(`api_proxy.php?endpoint=DeleteProduct&ItemCode=${encodeURIComponent(productCode)}`, {
      method: "POST",
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`)
        }
        return response.json()
      })
      .then((result) => {
        

        if (result.success) {
          showToast("Éxito", "Producto eliminado correctamente", "success")
          loadProducts() // Reload products table
        } else {
          showToast("Error", result.message || "No se pudo eliminar el producto", "error")
        }
      })
      .catch((error) => {
        console.error("Error deleting product:", error)
        showToast("Error", "No se pudo eliminar el producto: " + error.message, "error")
      })
      .finally(() => {
        toggleLoading(false)
      })
  }
}

// Function to save a product (create or update)
function saveProduct(event) {
  event.preventDefault()
  

  const productCode = document.getElementById("productCode").value
  const isNewProduct = !productCode
  

  // Collect form data
  const formData = new FormData(document.getElementById("productForm"))
  const productData = {}

  for (const [key, value] of formData.entries()) {
    productData[key] = value
  }

  // Handle checkbox for active status
  if (document.getElementById("productActive")) {
    productData.Active = document.getElementById("productActive").checked ? "1" : "0"
  }

  

  // Determine endpoint based on whether it's a new product or an update
  const endpoint = isNewProduct ? "CreateProduct" : "UpdateProduct"

  toggleLoading(true)
  
  fetch(`api_proxy.php?endpoint=${endpoint}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(productData),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((result) => {
      

      if (result.success) {
        showToast("Éxito", `Producto ${isNewProduct ? "creado" : "actualizado"} correctamente`, "success")
        // Declare jQuery if it's not already declared
        if (typeof jQuery === "undefined") {
          console.error("jQuery is not loaded!")
          showToast("Error", "jQuery no está cargado. Verifique las dependencias.", "error")
          return
        }
        jQuery("#productModal").modal("hide")
        loadProducts() // Reload products table
      } else {
        showToast("Error", result.message || `No se pudo ${isNewProduct ? "crear" : "actualizar"} el producto`, "error")
      }
    })
    .catch((error) => {
      console.error(`Error ${isNewProduct ? "creating" : "updating"} product:`, error)
      showToast("Error", `No se pudo ${isNewProduct ? "crear" : "actualizar"} el producto: ` + error.message, "error")
    })
    .finally(() => {
      toggleLoading(false)
    })
}

// Function to initialize the product maintenance section
function initProductMaintenance() {
  

  // Load initial data - this will load departments, categories, and then products in sequence
  loadProducts()

  // Set up event listeners
  const filterForm = document.getElementById("filterProductsForm")
  if (filterForm) {
    filterForm.addEventListener("submit", (event) => {
      event.preventDefault()
      loadProducts()
    })
  } else {
    console.warn("Filter form not found")
    // Add direct event listener to the search button as fallback
    const searchBtn = document.getElementById("applyProductFilters")
    if (searchBtn) {
      searchBtn.addEventListener("click", () => {
        
        loadProducts()
      })
    }
  }

  const resetFiltersBtn = document.getElementById("resetProductFilters")
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener("click", () => {
      if (document.getElementById("filterProductsForm")) {
        document.getElementById("filterProductsForm").reset()
      } else {
        // Reset individual filter fields if the form doesn't exist
      if (document.getElementById("productCodeFilter")) document.getElementById("productCodeFilter").value = ""
      if (document.getElementById("productNameFilter")) document.getElementById("productNameFilter").value = ""
      if (document.getElementById("productDepartmentFilter")) document.getElementById("productDepartmentFilter").value = ""
      if (document.getElementById("productCategoryFilter")) document.getElementById("productCategoryFilter").value = ""
      
      loadProducts()
      }
    })
  } else {
    console.warn("Reset filters button not found")
  }

  const addProductBtn = document.getElementById("addProductBtn")
  if (addProductBtn) {
    addProductBtn.addEventListener("click", () => {
      console.log("Add Product button clicked")
      document.getElementById("productForm").reset()
      document.getElementById("productFormTitle").textContent = "Agregar Producto"
      
      // Asegurarse de que el código de producto esté vacío para nuevos productos
      if (document.getElementById("productCode")) {
        document.getElementById("productCode").value = ""
        document.getElementById("productCode").removeAttribute("readonly") // Permitir editar código para nuevos productos
      }
      
      // Declare jQuery if it's not already declared
      if (typeof jQuery === "undefined") {
        console.error("jQuery is not loaded!")
        showToast("Error", "jQuery no está cargado. Verifique las dependencias.", "error")
        return
      }
      jQuery("#productModal").modal("show")
    })
  } else {
    console.warn("Add product button not found")
  }

  const productForm = document.getElementById("productForm")
  if (productForm) {
    productForm.addEventListener("submit", saveProduct)
  } else {
    console.warn("Product form not found")
  }
  
  // Añadir evento al botón de guardar dentro del modal
  const saveProductBtn = document.getElementById("saveProductBtn")
  if (saveProductBtn) {
    saveProductBtn.addEventListener("click", function() {
      const productForm = document.getElementById("productForm")
      if (productForm) {
        // Crear un evento para disparar el submit del formulario
        const event = new Event("submit", {
          bubbles: true,
          cancelable: true
        })
        productForm.dispatchEvent(event)
      }
    })
  }
}

// Función para probar la conectividad con la API
function testApiConnectivity() {
  

  // Test endpoints
  const endpoints = ["InventoryDepartments&Short=yes", "InventoryCategories&Short=yes", "GetAllProducts"]

  endpoints.forEach((endpoint) => {
    

    fetch(`api_proxy.php?endpoint=${endpoint}`)
      .then((response) => {
        
        return response.text()
      })
      .then((text) => {
         
        try {
          const json = JSON.parse(text)
          
        } catch (e) {
          console.error(`Endpoint ${endpoint} is not returning valid JSON:`, e)
        }
      })
      .catch((error) => {
        console.error(`Endpoint ${endpoint} error:`, error)
      })
  
  }) // <-- Close forEach
}

// Verificar los campos del formulario de producto
function validateProductForm() {
  const requiredFields = [
    { id: "productCode", name: "Código del producto" },
    { id: "productName", name: "Nombre del producto" },
    { id: "productPrice", name: "Precio" },
    { id: "productCost", name: "Costo" },
    { id: "productDepartment", name: "Departamento" },
    { id: "productCategory", name: "Categoría" }
  ]
  
  for (const field of requiredFields) {
    const element = document.getElementById(field.id)
    if (!element || !element.value.trim()) {
      showToast("Error de validación", `El campo ${field.name} es obligatorio`, "error")
      if (element) element.focus()
      return false
    }
  }
  
  // Validar que el precio y costo sean números válidos
  const price = parseFloat(document.getElementById("productPrice").value)
  const cost = parseFloat(document.getElementById("productCost").value)
  
  if (isNaN(price) || price < 0) {
    showToast("Error de validación", "El precio debe ser un número positivo", "error")
    document.getElementById("productPrice").focus()
    return false
  }
  
  if (isNaN(cost) || cost < 0) {
    showToast("Error de validación", "El costo debe ser un número positivo", "error")
    document.getElementById("productCost").focus()
    return false
  }
  
  return true
}

// Manejar cambio de departamento para filtrar categorías relacionadas
function handleDepartmentChange() {
  const departmentSelect = document.getElementById("productDepartment")
  const categorySelect = document.getElementById("productCategory")
  
  if (departmentSelect && categorySelect) {
    departmentSelect.addEventListener("change", function() {
      const departmentId = this.value
      
      // Limpiar las opciones actuales
      categorySelect.innerHTML = '<option value="">Seleccionar categoría</option>'
      
      if (!departmentId) return
      
      // Filtrar las categorías del departamento seleccionado
      const departmentCategories = productCategories.filter(
        category => category.DepartmentID === departmentId
      )
      
      // Agregar las categorías filtradas
      departmentCategories.forEach(category => {
        const option = document.createElement("option")
        option.value = category.CategoryID
        option.textContent = category.CategoryName
        categorySelect.appendChild(option)
      })
    })
  }
}

// Mejorar manejo de errores del servidor
function handleServerResponse(response, context) {
  if (response.success) {
    showToast("Éxito", `Operación de ${context} completada correctamente`, "success")
    return true
  } else {
    // Manejar diferentes tipos de errores del servidor
    if (response.errorCode) {
      switch (response.errorCode) {
        case "DUPLICATE_KEY":
          showToast("Error", `Ya existe un producto con este código`, "error")
          break
        case "NOT_FOUND":
          showToast("Error", `El producto no existe o fue eliminado`, "error")
          break
        case "INVALID_INPUT":
          showToast("Error", `Datos de entrada inválidos: ${response.message || "Verifique los campos"}`, "error")
          break
        case "PERMISSION_DENIED":
          showToast("Error", `No tiene permisos para realizar esta operación`, "error")
          break
        default:
          showToast("Error", response.message || `Error desconocido en la operación de ${context}`, "error")
      }
    } else {
      showToast("Error", response.message || `Error desconocido en la operación de ${context}`, "error")
    }
    return false
  }
}

// Función para subir imagen de producto
function uploadProductImage(productCode) {
  const fileInput = document.getElementById("productImage")
  if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
    console.log("No file selected, skipping image upload")
    return Promise.resolve(null)
  }
  
  const file = fileInput.files[0]
  const formData = new FormData()
  formData.append("image", file)
  formData.append("productCode", productCode)
  
  
  
  return fetch("api_proxy.php?endpoint=UploadProductImage", {
    method: "POST",
    body: formData
  })
    .then(response => {
      if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`)
      return response.json()
    })
    .then(result => {
      
      if (result.success) {
        showToast("Éxito", "Imagen subida correctamente", "success")
        return result.imageUrl
      } else {
        showToast("Advertencia", "No se pudo subir la imagen: " + (result.message || "Error desconocido"), "warning")
        return null
      }
    })
    .catch(error => {
      console.error("Error uploading image:", error)
      showToast("Error", "Error al subir la imagen: " + error.message, "error")
      return null
    })
}

// Función para mostrar o previsualizar la imagen del producto
function loadProductImage(productCode) {
  const imageContainer = document.getElementById("productImagePreview")
  if (!imageContainer) return
  
  // Mostrar indicador de carga
  imageContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando imagen...</div>'
  
  fetch(`api_proxy.php?endpoint=GetProductImage&ItemCode=${encodeURIComponent(productCode)}`)
    .then(response => {
      if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`)
      return response.json()
    })
    .then(result => {
      if (result.success && result.imageUrl) {
        imageContainer.innerHTML = `
          <div class="text-center">
            <img src="${result.imageUrl}" alt="Imagen del producto" class="img-fluid rounded product-image-preview" />
            <button class="btn btn-sm btn-danger mt-2" id="removeProductImage">
              <i class="fas fa-trash"></i> Eliminar imagen
            </button>
          </div>
        `
        
        // Añadir evento para eliminar imagen
        document.getElementById("removeProductImage").addEventListener("click", function() {
          if (confirm("¿Está seguro que desea eliminar la imagen?")) {
            deleteProductImage(productCode)
          }
        })
      } else {
        imageContainer.innerHTML = '<div class="text-center text-muted"><i class="fas fa-image"></i> Sin imagen</div>'
      }
    })
    .catch(error => {
      console.error("Error loading product image:", error)
      imageContainer.innerHTML = '<div class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar la imagen</div>'
    })
}

// Función para eliminar la imagen del producto
function deleteProductImage(productCode) {
  fetch(`api_proxy.php?endpoint=DeleteProductImage&ItemCode=${encodeURIComponent(productCode)}`, {
    method: "POST"
  })
    .then(response => {
      if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`)
      return response.json()
    })
    .then(result => {
      if (result.success) {
        showToast("Éxito", "Imagen eliminada correctamente", "success")
        document.getElementById("productImagePreview").innerHTML = 
          '<div class="text-center text-muted"><i class="fas fa-image"></i> Sin imagen</div>'
      } else {
        showToast("Error", result.message || "No se pudo eliminar la imagen", "error")
      }
    })
    .catch(error => {
      console.error("Error deleting product image:", error)
      showToast("Error", "Error al eliminar la imagen: " + error.message, "error")
    })
}

// Función para previsualizar la imagen seleccionada antes de subirla
function setupImagePreview() {
  const fileInput = document.getElementById("productImage")
  const previewContainer = document.getElementById("productImagePreview")
  
  if (fileInput && previewContainer) {
    fileInput.addEventListener("change", function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader()
        
        reader.onload = function(e) {
          previewContainer.innerHTML = `
            <div class="text-center">
              <img src="${e.target.result}" alt="Vista previa" class="img-fluid rounded product-image-preview" />
              <p class="text-muted small mt-1">Vista previa (no guardada)</p>
            </div>
          `
        }
        
        reader.readAsDataURL(this.files[0])
      } else {
        // Si no hay archivo seleccionado y hay código de producto, intentar cargar la imagen existente
        const productCode = document.getElementById("productCode").value
        if (productCode) {
          loadProductImage(productCode)
        } else {
          previewContainer.innerHTML = '<div class="text-center text-muted"><i class="fas fa-image"></i> Sin imagen</div>'
        }
      }
    })
  }
}

// Initialize when the DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  

  // Check if we're on the products maintenance page
  if (document.getElementById("productsMaintenanceTable")) {
    
    initProductMaintenance()
    setupImagePreview()
    handleDepartmentChange()
    
    // Modificar el formulario de producto para manejar la validación
    const productForm = document.getElementById("productForm")
    if (productForm) {
      productForm.addEventListener("submit", function(event) {
        event.preventDefault()
        
        if (validateProductForm()) {
          saveProduct(event)
        }
      })
    }
  } else {
    console.log("Products maintenance table not found, skipping initialization")
  }
})

// Add a global error handler
window.addEventListener("error", (event) => {
  console.error("Global error caught:", event.error)
  showToast("Error", "Se produjo un error en la aplicación. Consulte la consola para más detalles.", "error")
})

// Add unhandled promise rejection handler
window.addEventListener("unhandledrejection", (event) => {
  console.error("Unhandled promise rejection:", event.reason)
  showToast("Error", "Se produjo un error en una operación asíncrona. Consulte la consola para más detalles.", "error")
})

