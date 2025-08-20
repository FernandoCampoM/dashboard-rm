// js/maintenance.js

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

// Function to show toast notifications
function showToast(title, message, type) {
  // If you're using a toast library, implement this
  // Otherwise, you can use alert for now
  alert(`${title}: ${message}`)
}

// Function to load products from the API
function loadProducts() {
  toggleLoading(true)

  // Obtener valores de filtros
  const codeFilter = document.getElementById("productCodeFilter").value
  const nameFilter = document.getElementById("productNameFilter").value
  const departmentFilter = document.getElementById("productDepartmentFilter").value
  const categoryFilter = document.getElementById("productCategoryFilter").value

  // Construir parámetros de consulta
  let queryParams = ""
  if (codeFilter) queryParams += `&ItemCode=${encodeURIComponent(codeFilter)}`
  if (nameFilter) queryParams += `&Description=${encodeURIComponent(nameFilter)}`
  if (departmentFilter) queryParams += `&Department=${encodeURIComponent(departmentFilter)}`
  if (categoryFilter) queryParams += `&Category=${encodeURIComponent(categoryFilter)}`

  fetch(`api_proxy.php?endpoint=GetAllProducts${queryParams}`)
    .then((response) => response.json())
    .then((data) => {
      if (data && Array.isArray(data)) {
        // Preparar datos para la tabla
        const tableData = data.map((product) => {
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
          { title: "Estado", data: 8 },
          { title: "Acciones", data: 9 },
        ]

        // Inicializar o actualizar la tabla
        if ($.fn.DataTable.isDataTable("#productsMaintenanceTable")) {
          productsMaintenanceTable.clear().rows.add(tableData).draw()
        } else {
          productsMaintenanceTable = $("#productsMaintenanceTable").DataTable({
            data: tableData,
            columns: columns,
            language: {
              url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-AR.json",
            },
            responsive: true,
          })

          // Añadir eventos a los botones de acción
          $("#productsMaintenanceTable").on("click", ".edit-product", function () {
            const productCode = $(this).data("id")
            editProduct(productCode)
          })

          $("#productsMaintenanceTable").on("click", ".delete-product", function () {
            const productCode = $(this).data("id")
            deleteProduct(productCode)
          })
        }
      }
    })
    .catch((error) => {
      console.error("Error cargando productos:", error)
      showToast("Error", "No se pudieron cargar los productos", "error")
    })
    .finally(() => {
      toggleLoading(false)
    })
}

// Function to load product departments
function loadProductDepartments() {
  toggleLoading(true)

  fetch("api_proxy.php?endpoint=InventoryDepartments&Short=yes")
    .then((response) => response.json())
    .then((data) => {
      if (data && Array.isArray(data)) {
        productDepartments = data

        // Llenar el select de departamentos para filtros
        const filterSelect = document.getElementById("productDepartmentFilter")
        filterSelect.innerHTML = '<option value="">Todos los departamentos</option>'

        // Llenar el select de departamentos para el formulario
        const formSelect = document.getElementById("productDepartment")
        if (formSelect) {
          formSelect.innerHTML = '<option value="">Seleccionar departamento</option>'
        }

        data.forEach((department) => {
          const filterOption = document.createElement("option")
          filterOption.value = department.DepartmentID
          filterOption.textContent = department.DepartmentName
          filterSelect.appendChild(filterOption)

          if (formSelect) {
            const formOption = document.createElement("option")
            formOption.value = department.DepartmentID
            formOption.textContent = department.DepartmentName
            formSelect.appendChild(formOption)
          }
        })
      }
    })
    .catch((error) => {
      console.error("Error cargando departamentos de productos:", error)
      showToast("Error", "No se pudieron cargar los departamentos de productos", "error")
    })
    .finally(() => {
      toggleLoading(false)
    })
}

// Function to load product categories
function loadProductCategories() {
  toggleLoading(true)

  fetch("api_proxy.php?endpoint=InventoryCategories&Short=yes")
    .then((response) => response.json())
    .then((data) => {
      if (data && Array.isArray(data)) {
        productCategories = data

        // Llenar el select de categorías para filtros
        const filterSelect = document.getElementById("productCategoryFilter")
        filterSelect.innerHTML = '<option value="">Todas las categorías</option>'

        // Llenar el select de categorías para el formulario
        const formSelect = document.getElementById("productCategory")
        if (formSelect) {
          formSelect.innerHTML = '<option value="">Seleccionar categoría</option>'
        }

        data.forEach((category) => {
          const filterOption = document.createElement("option")
          filterOption.value = category.CategoryID
          filterOption.textContent = category.CategoryName
          filterSelect.appendChild(filterOption)

          if (formSelect) {
            const formOption = document.createElement("option")
            formOption.value = category.CategoryID
            formOption.textContent = category.CategoryName
            formSelect.appendChild(formOption)
          }
        })
      }
    })
    .catch((error) => {
      console.error("Error cargando categorías de productos:", error)
      showToast("Error", "No se pudieron cargar las categorías de productos", "error")
    })
    .finally(() => {
      toggleLoading(false)
    })
}

// Function to edit a product
function editProduct(productCode) {
  // Implement your edit product functionality here
  console.log("Editing product:", productCode)

  // Example implementation:
  fetch(`api_proxy.php?endpoint=GetProduct&ItemCode=${encodeURIComponent(productCode)}`)
    .then((response) => response.json())
    .then((product) => {
      if (product) {
        // Fill form with product data
        document.getElementById("productForm").reset()
        document.getElementById("productCode").value = product.ProductCode || ""
        document.getElementById("productName").value = product.ProductName || ""
        document.getElementById("productBarCode").value = product.BarCode || ""
        document.getElementById("productPrice").value = product.Price || ""
        document.getElementById("productCost").value = product.Cost || ""
        document.getElementById("productStock").value = product.CurrentStock || ""

        if (document.getElementById("productDepartment")) {
          document.getElementById("productDepartment").value = product.Department || ""
        }

        if (document.getElementById("productCategory")) {
          document.getElementById("productCategory").value = product.Category || ""
        }

        if (document.getElementById("productActive")) {
          document.getElementById("productActive").checked = product.Active === "1"
        }

        // Show form
        document.getElementById("productFormTitle").textContent = "Editar Producto"
        $("#productModal").modal("show")
      }
    })
    .catch((error) => {
      console.error("Error loading product details:", error)
      showToast("Error", "No se pudo cargar los detalles del producto", "error")
    })
}

// Function to delete a product
function deleteProduct(productCode) {
  // Implement your delete product functionality here
  console.log("Deleting product:", productCode)

  if (confirm("¿Está seguro que desea eliminar este producto?")) {
    fetch(`api_proxy.php?endpoint=DeleteProduct&ItemCode=${encodeURIComponent(productCode)}`, {
      method: "POST",
    })
      .then((response) => response.json())
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
        showToast("Error", "No se pudo eliminar el producto", "error")
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

  fetch(`api_proxy.php?endpoint=${endpoint}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(productData),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showToast("Éxito", `Producto ${isNewProduct ? "creado" : "actualizado"} correctamente`, "success")
        $("#productModal").modal("hide")
        loadProducts() // Reload products table
      } else {
        showToast("Error", result.message || `No se pudo ${isNewProduct ? "crear" : "actualizar"} el producto`, "error")
      }
    })
    .catch((error) => {
      console.error(`Error ${isNewProduct ? "creating" : "updating"} product:`, error)
      showToast("Error", `No se pudo ${isNewProduct ? "crear" : "actualizar"} el producto`, "error")
    })
}

// Function to initialize the product maintenance section
function initProductMaintenance() {
  // Load initial data
  loadProductDepartments()
  loadProductCategories()
  loadProducts()

  // Set up event listeners
  document.getElementById("filterProductsForm").addEventListener("submit", (event) => {
    event.preventDefault()
    loadProducts()
  })

  document.getElementById("resetProductFilters").addEventListener("click", () => {
    document.getElementById("filterProductsForm").reset()
    loadProducts()
  })

  document.getElementById("addProductBtn").addEventListener("click", () => {
    document.getElementById("productForm").reset()
    document.getElementById("productFormTitle").textContent = "Agregar Producto"
    $("#productModal").modal("show")
  })

  document.getElementById("productForm").addEventListener("submit", saveProduct)
}

// Import jQuery
import $ from "jquery"

// Initialize when the DOM is ready
$(document).ready(() => {
  // Check if we're on the products maintenance page
  if (document.getElementById("productsMaintenanceTable")) {
    initProductMaintenance()
  }
})
