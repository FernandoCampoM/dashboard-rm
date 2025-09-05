// js/clients.js

// Variables globales para almacenar categorías de clientes
let clientCategories = [];
let clientsTable;

// Función para formatear moneda
function formatCurrency(number) {
  return new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" }).format(number);
}

// Función para mostrar notificaciones toast con registro en consola
function showToast(title, message, type) {
  // Si estás usando una biblioteca de toast, implementa aquí
  // De lo contrario, puedes usar alert por ahora
  alert(`${title}: ${message}`);
}

// Función para activar/desactivar estado de carga
function toggleLoading(isLoading) {
  const loadingOverlay = document.getElementById("loadingOverlay");
  if (loadingOverlay) {
    loadingOverlay.style.display = isLoading ? "flex" : "none";
  }
}

// Función para extraer datos de la respuesta de la API
function extractApiData(data) {
  // Verifica si data tiene propiedad RESULT (estructura anidada)
  if (data && data.RESULT && Array.isArray(data.RESULT)) {
    // Si RESULT es un array de arrays, toma el primer array
    if (Array.isArray(data.RESULT[0])) {
      return data.RESULT[0];
    }
    // Si RESULT es solo un array de objetos, devuélvelo directamente
    return data.RESULT;
  }

  // Si data ya es un array, devuélvelo como está
  if (Array.isArray(data)) {
    return data;
  }

  // Si no podemos determinar la estructura, devolver array vacío
  console.error("Estructura de datos desconocida:", data);
  return [];
}

// Función para registrar respuestas de la API
function logApiResponse(endpoint, data) {
  const extractedData = extractApiData(data);
  if (!extractedData || extractedData.length === 0) {
    console.warn(`Advertencia: Datos vacíos extraídos de ${endpoint}`);
  }
  return extractedData;
}

// Función para cargar clientes desde la API
function loadClients() {
  
  // Cargar categorías primero para asegurar que las tenemos disponibles
  loadClientCategories(() => {
    loadClientsData();
  });
}

// Función que realmente carga los datos de clientes
function loadClientsData() {
  toggleLoading(true);

  // Obtener valores de filtros
  const nameFilter = document.getElementById("clientNameFilter") 
    ? document.getElementById("clientNameFilter").value 
    : "";
  const categoryFilter = document.getElementById("clientCategoryFilter")
    ? document.getElementById("clientCategoryFilter").value
    : "";
  const cityFilter = document.getElementById("clientCityFilter")
    ? document.getElementById("clientCityFilter").value
    : "";

 

  // Construir parámetros de consulta
  let queryParams = "";
  if (nameFilter) queryParams += `&Name=${encodeURIComponent(nameFilter)}`;
  if (categoryFilter) queryParams += `&Category=${encodeURIComponent(categoryFilter)}`;
  if (cityFilter) queryParams += `&City=${encodeURIComponent(cityFilter)}`;

  const apiUrl = `api_proxy.php?endpoint=Clients${queryParams}`;

  // Usar un timeout para evitar solicitudes colgadas
  const timeoutId = setTimeout(() => {
    toggleLoading(false);
    showToast("Error", "La solicitud ha tardado demasiado tiempo. Por favor, inténtelo de nuevo.", "error");
  }, 30000); // 30 segundos de timeout

  fetch(apiUrl)
    .then((response) => {
      clearTimeout(timeoutId); // Limpiar el timeout
      if (!response.ok) {
        throw new Error(`¡Error HTTP! Estado: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      const clients = logApiResponse("Clients", data);

      if (clients && clients.length > 0) {

        // Preparar datos para la tabla
        const tableData = clients.map((client) => {

          // Buscar el nombre de la categoría
          const category = clientCategories.find((c) => c.CategoryID === client.Category);
          const categoryName = category ? category.CategoryName : client.Category;

          // Crear botones de acción
          const editButton = `<button class="btn btn-sm btn-primary edit-client" data-id="${client.ClientID}"><i class="fas fa-edit"></i></button>`;
          const deleteButton = `<button class="btn btn-sm btn-danger ms-1 delete-client" data-id="${client.ClientID}"><i class="fas fa-trash"></i></button>`;

          return [
            client.ClientID || "",
            client.FirstName || "",
            client.LastName || "",
            client.City || "",
            client.Phone || "",
            client.Email || "",
            categoryName || "",
            client.Active === "S" ? 
              '<span class="badge bg-success">Activo</span>' : 
              '<span class="badge bg-danger">Inactivo</span>',
            editButton + deleteButton
          ];
        });


        try {
          // Verificar si existe el elemento de la tabla
          const tableElement = document.getElementById("clientsTable");
          if (!tableElement) {
            console.error("Elemento de tabla no encontrado");
            showToast("Error", "No se encontró la tabla de clientes", "error");
            return;
          }


          // Verificar si jQuery está disponible
          if (typeof jQuery === "undefined") {
            console.error("¡jQuery no está cargado!");
            showToast("Error", "jQuery no está cargado. Verifique las dependencias.", "error");
            return;
          }

          // Usar jQuery de forma segura
          const $ = jQuery;

          // Verificar si DataTables está disponible
          if (!$.fn.DataTable) {
            console.error("¡DataTables no está cargado!");
            showToast("Error", "DataTables no está cargado. Verifique las dependencias.", "error");
            return;
          }

          // Definir columnas con ancho y alineación adecuados
          const columns = [
            { title: "ID", data: 0, width: "5%" },
            { title: "Nombre", data: 1, width: "15%" },
            { title: "Apellido", data: 2, width: "15%" },
            { title: "Ciudad", data: 3, width: "10%" },
            { title: "Teléfono", data: 4, width: "12%" },
            { title: "Email", data: 5, width: "15%" },
            { title: "Categoría", data: 6, width: "10%" },
            { title: "Estado", data: 7, width: "8%", className: "text-center" },
            { title: "Acciones", data: 8, width: "10%", className: "text-center", orderable: false }
          ];

          // Inicializar o actualizar la tabla
          if ($.fn.DataTable.isDataTable("#clientsTable")) {
            $("#clientsTable").DataTable().clear().rows.add(tableData).draw();
          } else {
            clientsTable = $("#clientsTable").DataTable({
              data: tableData,
              columns: columns,
              language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-AR.json",
              },
              responsive: true,
              autoWidth: false, // Desactivar cálculo automático de ancho
              columnDefs: [
                { responsivePriority: 1, targets: [0, 1, 8] }, // Estas columnas son las más importantes
                { responsivePriority: 2, targets: [4, 7] }     // Estas columnas son las siguientes más importantes
              ],
              dom:
                '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
              initComplete: function () {
                // Añadir algo de estilo personalizado después de inicializar la tabla
                $(this).closest(".dataTables_wrapper").addClass("card-body p-0");
              },
            });

            // Añadir eventos a los botones de acción - solo añadir una vez
            $(document).on("click", ".edit-client", function () {
              const clientId = $(this).data("id");
              editClient(clientId);
            });

            $(document).on("click", ".delete-client", function () {
              const clientId = $(this).data("id");
              deleteClient(clientId);
            });
          }

        } catch (error) {
          console.error("Error al inicializar DataTable:", error);
          showToast("Error", "Error al inicializar la tabla de clientes: " + error.message, "error");
        }
      } else {
        console.error("No se encontraron clientes en los datos:", data);
        showToast("Información", "No se encontraron clientes con los filtros seleccionados", "info");
      }
    })
    .catch((error) => {
      clearTimeout(timeoutId); // Limpiar el timeout
      console.error("Error cargando clientes:", error);
      showToast("Error", "No se pudieron cargar los clientes: " + error.message, "error");
    })
    .finally(() => {
      toggleLoading(false);
    });
}

// Función para cargar categorías de clientes
function loadClientCategories(callback) {
  toggleLoading(true);

  // Usar un timeout para evitar solicitudes colgadas
  const timeoutId = setTimeout(() => {
    toggleLoading(false);
    showToast("Error", "La solicitud de categorías ha tardado demasiado tiempo.", "error");
  }, 30000); // 30 segundos de timeout

  const apiUrl = "api_proxy.php?endpoint=ClientCategories";

  fetch(apiUrl)
    .then((response) => {
      clearTimeout(timeoutId); // Limpiar el timeout
      if (!response.ok) {
        throw new Error(`¡Error HTTP! Estado: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      const categories = logApiResponse("ClientCategories", data);

      if (categories && categories.length > 0) {
        clientCategories = categories;

        // Llenar el select de categorías para filtros
        const filterSelect = document.getElementById("clientCategoryFilter");
        if (filterSelect) {
          filterSelect.innerHTML = '<option value="">Todas las categorías</option>';

          categories.forEach((category) => {
            const filterOption = document.createElement("option");
            filterOption.value = category.CategoryID;
            filterOption.textContent = category.CategoryName;
            filterSelect.appendChild(filterOption);
          });
        } else {
          console.warn("Select de filtro de categorías no encontrado");
        }

        // Llenar el select de categorías para el formulario
        const formSelect = document.getElementById("clientCategory");
        if (formSelect) {
          formSelect.innerHTML = '<option value="">Seleccionar categoría</option>';

          categories.forEach((category) => {
            const formOption = document.createElement("option");
            formOption.value = category.CategoryID;
            formOption.textContent = category.CategoryName;
            formSelect.appendChild(formOption);
          });
        }

        // Llamar a la función callback si se proporciona
        if (typeof callback === "function") {
          callback();
        }
      } else {
        console.error("No se encontraron categorías en los datos:", data);
        showToast("Error", "No se encontraron categorías de clientes", "error");

        // Llamar a la función callback incluso en caso de error para continuar el flujo
        if (typeof callback === "function") {
          callback();
        }
      }
    })
    .catch((error) => {
      clearTimeout(timeoutId); // Limpiar el timeout
      console.error("Error cargando categorías de clientes:", error);
      showToast("Error", "No se pudieron cargar las categorías: " + error.message, "error");

      // Llamar a la función callback incluso en caso de error para continuar el flujo
      if (typeof callback === "function") {
        callback();
      }
    })
    .finally(() => {
      toggleLoading(false);
    });
}

// Función para editar un cliente
function editClient(clientId) {
  
  // Validar ID de cliente
  if (!clientId) {
    console.error("No se proporcionó un ID de cliente");
    showToast("Error", "No se proporcionó un ID de cliente válido", "error");
    return;
  }
  
  // Mostrar indicador de carga
  toggleLoading(true);
  
  // Intentar diferentes formatos de endpoint de API
  const apiEndpoints = [
    `api_proxy.php?endpoint=GetClient&ClientID=${encodeURIComponent(clientId)}`,
    `api_proxy.php?endpoint=GetClientDetails&ClientID=${encodeURIComponent(clientId)}`,
    `api_proxy.php?endpoint=Clients&ClientID=${encodeURIComponent(clientId)}`
  ];
  
  
  // Probar cada endpoint en secuencia
  tryNextEndpoint(0);
  
  function tryNextEndpoint(index) {
    if (index >= apiEndpoints.length) {
      console.error("Todos los endpoints de API fallaron");
      toggleLoading(false);
      showToast("Error", "No se pudo cargar los detalles del cliente después de intentar múltiples endpoints", "error");
      return;
    }
    
    const apiUrl = apiEndpoints[index];
    
    fetch(apiUrl)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`¡Error HTTP! Estado: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        
        // Si data es false o vacío, probar el siguiente endpoint
        if (!data || (typeof data === "object" && Object.keys(data).length === 0)) {
          tryNextEndpoint(index + 1);
          return;
        }
        
        // Intentar extraer el cliente de diferentes estructuras de datos
        const client = extractClientFromResponse(data, clientId);
        
        if (client) {
          populateClientForm(client);
        } else {
          tryNextEndpoint(index + 1);
        }
      })
      .catch((error) => {
        console.error(`Error con endpoint ${index + 1}:`, error);
        tryNextEndpoint(index + 1);
      });
  }
  
  // Función auxiliar para extraer cliente de diferentes estructuras de respuesta
  function extractClientFromResponse(data, clientId) {
    // Caso 1: data.RESULT es un array de clientes
    if (data.RESULT && Array.isArray(data.RESULT)) {
      // Si es un array de arrays, tomar el primer array
      if (Array.isArray(data.RESULT[0])) {
        const clients = data.RESULT[0];
        return clients.find((c) => c.ClientID === clientId);
      }
      // Si RESULT es solo un array de objetos, buscar en él
      return data.RESULT.find((c) => c.ClientID === clientId);
    }
    
    // Caso 2: data es un array de clientes
    if (Array.isArray(data)) {
      return data.find((c) => c.ClientID === clientId);
    }
    
    // Caso 3: data es el objeto cliente en sí
    if (typeof data === "object" && data !== null) {
      if (data.ClientID === clientId) {
        return data;
      }
    }
    
    return null;
  }
  
  // Función auxiliar para poblar el formulario con datos del cliente
  function populateClientForm(client) {
    // Resetear el formulario
    document.getElementById("clientForm").reset();
    
    
    // Mapear los nombres de campo de la API a los IDs de campo del formulario
    const fieldMappings = {
      ClientID: "clientId",
      FirstName: "clientName",
      LastName: "clientLastName",
      Address1: "clientAddress1",
      Address2: "clientAddress2",
      City: "clientCity",
      ZipCode: "clientZipCode",
      Country: "clientCountry",
      Phone: "clientPhone",
      Email: "clientEmail",
      Category: "clientCategory",
      Active: "clientActive"
    };
    
    
    // Establecer valores del formulario basados en los mapeos
    for (const [apiField, formField] of Object.entries(fieldMappings)) {
      const formElement = document.getElementById(formField);
      if (formElement && client[apiField] !== undefined) {
        formElement.value = client[apiField] || "";
      }
    }
    
    // Mostrar formulario
    document.getElementById("clientModalLabel").textContent = "Editar Cliente";
    
    // Verificar si jQuery está definido
    if (typeof jQuery === "undefined") {
      console.error("¡jQuery no está cargado!");
      showToast("Error", "jQuery no está cargado. Verifique las dependencias.", "error");
      return;
    }
    
    jQuery("#clientModal").modal("show");
    toggleLoading(false);
  }
}

// Función para eliminar un cliente
function deleteClient(clientId) {
  
  if (confirm("¿Está seguro que desea eliminar este cliente?")) {
    toggleLoading(true);
    
    fetch(`api_proxy.php?endpoint=DeleteClient&ClientID=${encodeURIComponent(clientId)}`, {
      method: "POST"
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`¡Error HTTP! Estado: ${response.status}`);
        }
        return response.json();
      })
      .then((result) => {
        
        if (result.success) {
          showToast("Éxito", "Cliente eliminado correctamente", "success");
          loadClients(); // Recargar tabla de clientes
        } else {
          showToast("Error", result.message || "No se pudo eliminar el cliente", "error");
        }
      })
      .catch((error) => {
        showToast("Error", "No se pudo eliminar el cliente: " + error.message, "error");
      })
      .finally(() => {
        toggleLoading(false);
      });
  }
}

// Función para guardar un cliente (crear o actualizar)
function saveClient(event) {
  event.preventDefault();
  console.log("ID del cliente:", clientId, "Es nuevo:", isNewClient);
  const clientId = document.getElementById("clientId").value;
  const isNewClient = !clientId;
  
  // Recopilar datos del formulario
  const clientData = {
    ClientID: clientId,
    FirstName: document.getElementById("clientName").value,
    LastName: document.getElementById("clientLastName").value,
    Address1: document.getElementById("clientAddress1").value,
    Address2: document.getElementById("clientAddress2").value,
    City: document.getElementById("clientCity").value,
    ZipCode: document.getElementById("clientZipCode").value,
    Country: document.getElementById("clientCountry").value,
    Phone: document.getElementById("clientPhone").value,
    Email: document.getElementById("clientEmail").value,
    Category: document.getElementById("clientCategory").value,
    Active: document.getElementById("clientActive").value
  };
  
  
  // Determinar endpoint basado en si es un cliente nuevo o una actualización
  const endpoint = isNewClient ? "CreateClient" : "UpdateClient";
  
  toggleLoading(true);
  
  fetch(`api_proxy.php?endpoint=${endpoint}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(clientData)
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`¡Error HTTP! Estado: ${response.status}`);
      }
      return response.json();
    })
    .then((result) => {
      
      if (result.success) {
        showToast("Éxito", `Cliente ${isNewClient ? "creado" : "actualizado"} correctamente`, "success");
        
        // Verificar si jQuery está definido
        if (typeof jQuery === "undefined") {
          console.error("¡jQuery no está cargado!");
          showToast("Error", "jQuery no está cargado. Verifique las dependencias.", "error");
          return;
        }
        
        jQuery("#clientModal").modal("hide");
        loadClients(); // Recargar tabla de clientes
      } else {
        showToast("Error", result.message || `No se pudo ${isNewClient ? "crear" : "actualizar"} el cliente`, "error");
      }
    })
    .catch((error) => {
      console.error(`Error ${isNewClient ? "creando" : "actualizando"} cliente:`, error);
      showToast("Error", `No se pudo ${isNewClient ? "crear" : "actualizar"} el cliente: ` + error.message, "error");
    })
    .finally(() => {
      toggleLoading(false);
    });
}

// Función para inicializar la sección de mantenimiento de clientes
function initClientMaintenance() {
  
  // Cargar datos iniciales
  loadClients();
  
  // Configurar escuchadores de eventos
  const filterForm = document.getElementById("filterClientsForm");
  if (filterForm) {
    filterForm.addEventListener("submit", (event) => {
      event.preventDefault();
      loadClients();
    });
  }
  
  // Si no hay formulario de filtro, agregar escuchador al botón de búsqueda
  const applyFiltersBtn = document.getElementById("applyClientFilters");
  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener("click", () => {
      loadClients();
    });
  }
  
  const resetFiltersBtn = document.getElementById("resetClientFilters");
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener("click", () => {
      // Si existe el formulario, limpiarlo
      if (filterForm) {
        filterForm.reset();
      } else {
        // Si no, limpiar los campos individuales
        const nameFilter = document.getElementById("clientNameFilter");
        if (nameFilter) nameFilter.value = "";
        
        const categoryFilter = document.getElementById("clientCategoryFilter");
        if (categoryFilter) categoryFilter.value = "";
        
        const cityFilter = document.getElementById("clientCityFilter");
        if (cityFilter) cityFilter.value = "";
      }
      loadClients();
    });
  }
  
  const addClientBtn = document.getElementById("addClientBtn");
  if (addClientBtn) {
    addClientBtn.addEventListener("click", () => {
      document.getElementById("clientForm").reset();
      document.getElementById("clientModalLabel").textContent = "Agregar Cliente";
      document.getElementById("clientId").value = ""; // Asegurarse de que el ID esté vacío para nuevos clientes
      
      // Verificar si jQuery está definido
      if (typeof jQuery === "undefined") {
        console.error("¡jQuery no está cargado!");
        showToast("Error", "jQuery no está cargado. Verifique las dependencias.", "error");
        return;
      }
      
      jQuery("#clientModal").modal("show");
    });
  }
  
  const clientForm = document.getElementById("clientForm");
  if (clientForm) {
    clientForm.addEventListener("submit", saveClient);
  }
  
  const saveClientBtn = document.getElementById("saveClientBtn");
  if (saveClientBtn) {

    saveClientBtn.addEventListener("click", function() {
      const clientForm = document.getElementById("clientForm");
      if (clientForm) {
        console.log("Disparando submit del formulario de cliente");
        // Crear un evento para disparar el submit del formulario
        const event = new Event("submit", {
          bubbles: true,
          cancelable: true
        });
        clientForm.dispatchEvent(event);
      }
    });
  }
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  
  // Verificar si estamos en la página de mantenimiento de clientes
  if (document.getElementById("clientsTable")) {
    initClientMaintenance();
  } else {
    console.log("Tabla de clientes no encontrada, omitiendo inicialización");
  }
});

// Añadir manejador de errores global
window.addEventListener("error", (event) => {
  console.error("Error global capturado:", event.error);
  showToast("Error", "Se produjo un error en la aplicación. Consulte la consola para más detalles.", "error");
});

// Añadir manejador de rechazos de promesa no manejados
window.addEventListener("unhandledrejection", (event) => {
  console.error("Rechazo de promesa no manejado:", event.reason);
  showToast("Error", "Se produjo un error en una operación asíncrona. Consulte la consola para más detalles.", "error");
});
