/* global FullCalendar */
let calendar;
let selectedEvent = null; 

document.addEventListener("DOMContentLoaded", function () {
    
    var calendarEl = document.getElementById("calendar");
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek', 
        locale: 'es',
        selectable: true,
        editable: true,
        expandRows: true,
        allDaySlot: false,
        slotMinTime: "07:00:00",
        slotMaxTime: "22:00:00",
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        events: [],

        select: function (info) {
          /*let title = prompt('Nombre de la actividad:');
          if (title) {
            calendar.addEvent({
              title: title,
              start: info.startStr,
              end: info.endStr,
              allDay: false 
            });
          }
          calendar.unselect();*/
            // Guardamos las fechas seleccionadas en inputs del formulario
            document.getElementById("eventStart").value = info.startStr.slice(0, 16); // yyyy-MM-ddTHH:mm
            document.getElementById("eventEnd").value = info.endStr.slice(0, 16);

            // Limpiar el título
            document.getElementById("eventTitle").value = "";

            // Mostrar el modal
            const modalElement = document.getElementById("eventModal");
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();

            // Guardamos las fechas seleccionadas para usarlas luego al guardar
            calendar.unselect();
        },

        eventClick: function (info) {
          /*if (confirm("¿Eliminar este evento?")) {
            info.event.remove();
          }*/
            selectedEvent = info.event; // Guardamos el evento seleccionado

            // Precargamos datos en el formulario
            document.getElementById("eventTitle").value = selectedEvent.title;
            document.getElementById("eventStart").value = selectedEvent.startStr.slice(0, 16);
            document.getElementById("eventEnd").value = selectedEvent.endStr ? selectedEvent.endStr.slice(0, 16) : "";
            document.getElementById("eventColor").value = selectedEvent.backgroundColor || "#1e90ff";

            // Abrimos el modal
            const modalElement = document.getElementById("eventModal");
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
        },
        
        eventDrop: function (info) {
            const event = info.event;

            console.log(info.event.startStr.slice(0, 19));

            // Datos actualizados
            const updatedEvent = {
              id: event.id,
              title: event.title,
              dateStart: event.startStr.slice(0, 19).replace("T", " "),
              dateEnd: event.endStr ? event.endStr.slice(0, 19).replace("T", " ") : "",
              color: event.backgroundColor || "#1e90ff",
              employeeID: 1
            };

            console.log("Evento movido:", updatedEvent);

            // Llamada PUT al API para guardar los cambios
            fetch(`http://localhost:50003/api/schedule/${event.id}`, {
              method: "PUT",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(updatedEvent)
            })
              .then(res => {
                if (!res.ok) throw new Error("Error al actualizar evento");
                return res.json();
              })
              .then(data => {
                console.log("Evento actualizado en backend:", data);
              })
              .catch(err => {
                console.error("Error al actualizar:", err);
                alert("No se pudo actualizar el evento. Se revertirá el cambio.");
                info.revert(); // 👈 Revierte el cambio en el calendario
              });
        },
        
        
        eventResize: function (info) {
            const event = info.event;

            const updatedEvent = {
              id: event.id,
              title: event.title,
              dateStart: event.startStr.slice(0, 19).replace("T", " "),
              dateEnd: event.endStr.slice(0, 19).replace("T", " "),
              color: event.backgroundColor || "#1e90ff",
              employeeID: 1
            };

            fetch(`http://localhost:50003/api/schedule/${event.id}`, {
              method: "PUT",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(updatedEvent)
            })
              .then(res => {
                if (!res.ok) throw new Error("Error al actualizar duración del evento");
                return res.json();
              })
              .then(data => console.log("Evento redimensionado actualizado:", data))
              .catch(err => {
                console.error("Error:", err);
                alert("No se pudo actualizar el evento.");
                info.revert();
              });
        }
    });

    calendar.render();
    reloadCalendarEvents();
});

function showHorario() {
  const section = document.getElementById("horario-section");
  section.classList.remove("d-none");


  setTimeout(() => {
    calendar.updateSize();
    calendar.changeView("timeGridWeek");
  }, 50);
}

function closeModal() {
  const modalElement = document.getElementById("eventModal");
  const modalInstance = bootstrap.Modal.getInstance(modalElement);
  modalInstance.hide();

  document.body.classList.remove("modal-open");
  const backdrops = document.getElementsByClassName("modal-backdrop");
  while (backdrops.length > 0) {
    backdrops[0].parentNode.removeChild(backdrops[0]);
  }

  selectedEvent = null; // Limpiamos el estado
}

document.getElementById("horario-link").addEventListener("click", function () {
  showHorario();
});

const eventForm = document.getElementById("eventForm");

eventForm.addEventListener("submit", function (e) {
    e.preventDefault(); // Evita el reload del formulario

    // Obtener valores del formulario
    const title = document.getElementById("eventTitle").value.trim();
    const start = document.getElementById("eventStart").value;
    const end = document.getElementById("eventEnd").value;
    const color = document.getElementById("eventColor").value;

    if (!title || !start || !end) {
      alert("Por favor completa todos los campos obligatorios.");
      return;
    }

    if (selectedEvent) {
        selectedEvent.setProp("title", title);
        selectedEvent.setStart(start);
        selectedEvent.setEnd(end);
        selectedEvent.setProp("backgroundColor", color);

        // PUT al backend
        fetch(`http://localhost:50003/api/schedule/${selectedEvent.id}`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            id: selectedEvent.id,
            title,
            dateStart: start.replace("T", " ") + ":00",
            dateEnd: end.replace("T", " ") + ":00",
            color,
            employeeID: 1
          })
        })
        .then(res => {
          if (!res.ok) throw new Error("Error al actualizar evento");
          return res.json();
        })
        .then(data => {
          console.log("Evento actualizado:", data);
          closeModal();
        })
        .catch(err => console.error("Error:", err));
    } 
    // Caso: crear nuevo evento
    else {
        const newEvent = {
          title,
          start,
          end,
          color
        };

        calendar.addEvent(newEvent);

        fetch("http://localhost:50003/api/schedule", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            title,
            dateStart: start.replace("T", " ") + ":00",
            dateEnd: end.replace("T", " ") + ":00",
            color,
            employeeID: 1
          })
        })
        .then(res => {
          if (!res.ok) throw new Error("Error al guardar evento");
          return res.json();
        })
        .then(data => {
          console.log("Evento creado:", data);
          closeModal();
        })
        .catch(err => console.error("Error:", err));
  }
});

const deleteBtn = document.getElementById("deleteEventBtn");

document.getElementById("eventModal").addEventListener("show.bs.modal", () => {
  deleteBtn.classList.toggle("d-none", !selectedEvent);
});

deleteBtn.addEventListener("click", () => {
  if (selectedEvent && confirm("¿Seguro que deseas eliminar este evento?")) {
    fetch(`http://localhost:50003/api/schedule/${selectedEvent.id}`, {
      method: "DELETE"
    })
      .then(res => {
        if (!res.ok) throw new Error("Error al eliminar evento");
        selectedEvent.remove();
        closeModal();
      })
      .catch(err => console.error("Error:", err));
  }
});


document.getElementById("btn-refresh").addEventListener("click", function () {
  reloadCalendarEvents();
});

function reloadCalendarEvents() {
  fetch("http://localhost:50003/api/schedule")
    .then(response => {
      if (!response.ok) {
        throw new Error(`Error al cargar los eventos: ${response.status} ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      // Limpiar eventos actuales
      calendar.removeAllEvents();

      // Mapear los eventos del API al formato que espera FullCalendar
      const mappedEvents = data.map(ev => ({
        id: ev.id,
        title: ev.title,
        start: ev.dateStart.replace(" ", "T"),
        end: ev.dateEnd.replace(" ", "T"),
        color: ev.color || "#0d6efd"
      }));

      // Agregar los nuevos eventos
      mappedEvents.forEach(ev => calendar.addEvent(ev));

      console.log("✅ Calendario actualizado con nuevos eventos:", mappedEvents);
    })
    .catch(error => {
      console.error("❌ Error al refrescar el calendario:", error.message);
      alert("No se pudieron recargar los eventos.");
    });
}
