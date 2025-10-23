const API_URL = "http://localhost:9192/api/availableSchedules";

/**
 * Obtiene todos los horarios
 */
export async function getAllAvailableSchedule() {
  try {
    const response = await fetch(API_URL);
    if (!response.ok) throw new Error(`Error al cargar: ${response.status}`);
    const data = await response.json();
    return data.map(ev => ({
      id: ev.asId,
      title: ev.title,
      duration: ev.duration,
      employeeID: ev.employeeID
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
export async function removeAvailableSchedule(id) {
  try {
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
export async function createAvailableSchedule(event) {
  try {
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
export async function updateAvailableSchedule(id, event) {
  try {
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