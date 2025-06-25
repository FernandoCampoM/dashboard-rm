// API Client para acceder directamente a los endpoints
// Nota: Exponer credenciales en el frontend no es seguro para producción
// Se recomienda usar el proxy PHP para entornos de producción

// Configuración de la API
const API_CONFIG = {
    baseUrl: "http://localhost:8082/cse.api.v1/",
    username: "testserver", // Reemplazar con credenciales reales
    password: "testserver", // Reemplazar con credenciales reales
  }
  
  /**
   * Función para realizar peticiones a la API con autenticación básica
   * @param {string} endpoint - El endpoint de la API
   * @param {Object} params - Parámetros opcionales
   * @returns {Promise} - Promesa con los datos de la respuesta
   */
  async function fetchFromAPI(endpoint, params = {}) {
    // Construir URL con parámetros
    const url = new URL(endpoint, API_CONFIG.baseUrl)
    Object.keys(params).forEach((key) => {
      if (params[key]) {
        url.searchParams.append(key, params[key])
      }
    })
  
    try {
      // Crear credenciales en formato Base64
      const credentials = btoa(`${API_CONFIG.username}:${API_CONFIG.password}`)
  
      // Realizar petición con autenticación básica
      const response = await fetch(url.toString(), {
        method: "GET",
        headers: {
          Accept: "application/json",
          Authorization: `Basic ${credentials}`,
        },
      })
  
      // Verificar si la respuesta es exitosa
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`)
      }
  
      // Obtener texto de la respuesta
      const text = await response.text()
  
      // Verificar si es 'NoMatch'
      if (text === "NoMatch") {
        return "NoMatch"
      }
  
      // Intentar parsear como JSON
      try {
        return JSON.parse(text)
      } catch (e) {
        // Si no es JSON válido, devolver el texto
        return text
      }
    } catch (error) {
      console.error(`Error llamando a ${endpoint}:`, error)
      throw error
    }
  }
  
  // Ejemplo de uso:
  // fetchFromAPI('InfoCompany')
  //     .then(data => console.log(data))
  //     .catch(error => console.error('Error:', error));
  
  