async function getApiUrl() {
  try {
    const res = await fetch("setup/get_config.php");
    const data = await res.json();

    if (data.status === "ok" && data.config) {
      const ip = data.config.backend_ip || '';
      const port = data.config.backend_port || '';
      return `http://${ip}:9192/api/schedule`;
    } else {
      return "http://localhost:9192/api/schedule";
    }
  } catch (err) {
    console.error("Error al leer configuración del servidor:", err);
    return null;
  }
}

/**
 * Obtiene todos los horarios
 */
export async function getAll() {
  try {
    const API_URL = await getApiUrl();

    const response = await fetch(API_URL);
    if (!response.ok) throw new Error(`Error al cargar: ${response.status}`);
    const data = await response.json();
    return data.map(ev => ({
        id: ev.id,
        title: ev.title,
        start: ev.dateStart.replace(" ", "T"),
        end: ev.dateEnd.replace(" ", "T"),
        color: ev.color || "#0d6efd",
        extendedProps: {
          employeeID: ev.employeeID
        }
    }));
  } catch (error) {
    console.error("Error en getAll:", error);
    return [];
  }
}

/**
 * Elimina un evento
 * @param {type} id
 * @returns {Boolean}
 */
export async function remove(id) {
  try {
    const API_URL = await getApiUrl();
    const response = await fetch(`${API_URL}/${id}`, { method: "DELETE" });
    if (!response.ok) throw new Error("Error al eliminar evento");
    return true;
  } catch (error) {
    console.error("Error en delete:", error);
    throw error;
  }
}

/**
 * Crea un nuevo evento
 * @param {type} event
 * @returns {unresolved}
 */
export async function create(event) {
  try {
    const API_URL = await getApiUrl();
    const response = await fetch(API_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(event)
    });
    if (!response.ok) throw new Error("Error al crear evento");
    return await response.json();
  } catch (error) {
    console.error("Error en create:", error);
    throw error;
  }
}

/**
 * Actualiza un evento existente
 * @param {type} id
 * @param {type} event
 * @returns {unresolved}
 */
export async function update(id, event) {
  try {
    const API_URL = await getApiUrl();
    const response = await fetch(`${API_URL}/${id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(event)
    });
    if (!response.ok) throw new Error("Error al actualizar evento");
    return await response.json();
  } catch (error) {
    console.error("Error en update:", error);
    throw error;
  }
}