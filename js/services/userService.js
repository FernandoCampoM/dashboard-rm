const API_URL = "http://104.207.132.136:8180/cse.api.v1/GetEmployees";

/**
 * Obtiene todos los usuarios
 */
export async function getAllUsers() {
  try {
    const response = await fetch(API_URL);
    if (!response.ok) throw new Error(`Error al cargar: ${response.status}`);
    const data = await response.json();
    return data.map(ev => ({
        id: ev.ID,
        name: ev.Name,
        securityLevel: ev.SecurityLevel,
        acces: ev.Acces,
        salesman: ev.Salesman
    }));
  } catch (error) {
    console.error("Error en getAll:", error);
    return [];
  }
}