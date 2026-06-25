
async function getApiUrl() {
  try {
    const res = await fetch("setup/get_config.php");
    const data = await res.json();

    if (data.status === "ok" && data.config) {
      const ip = data.config.backend_ip || '';
      const port = data.config.backend_port || '';
      return `http://${ip}:${port}/cse.api.v1/GetEmployees`;
    } else {
      return "http://localhost:9192/cse.api.v1/GetEmployees";
    }
  } catch (err) {
    console.error("Error al leer configuración del servidor:", err);
    return null;
  }
}
/**
 * Obtiene todos los usuarios
 */
export async function getAllUsers() {
  try {
    const API_URL = await getApiUrl();
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