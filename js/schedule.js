import { getAll, remove, create, update } from "./services/scheduleService.js";
import { toScheduleCalendarDto } from "./factory/scheduleFactory.js";

/* global FullCalendar */
let calendar;
let selectedEvent = null; 

/**
 * Calendar
 */
document.addEventListener("DOMContentLoaded", function () {
    
    let containerEl = document.getElementById('external-events');
    new FullCalendar.Draggable(containerEl, {
      itemSelector: '.fc-event',
      eventData: function (eventEl) {
        return {
          title: eventEl.getAttribute('data-title'),
          duration: '02:00' // duración por defecto de 1 hora
        };
      }
    });
    
    var calendarEl = document.getElementById("calendar");
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek', 
        locale: 'es',
        selectable: true,
        editable: true,
        expandRows: true,
        droppable: true,
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

/**
 * Renderiza el calendario
 * @returns {undefined}
 */
function showHorario() {
  const section = document.getElementById("horario-section");
  section.classList.remove("d-none");


  setTimeout(() => {
    calendar.updateSize();
    calendar.changeView("timeGridWeek");
  }, 50);
}

document.getElementById("horario-link").addEventListener("click", function () {
  showHorario();
});

/**
 * Crea y Actualiza
 */
const eventForm = document.getElementById("eventForm");

eventForm.addEventListener("submit", function (e) {
    e.preventDefault(); 

    const schedule = getSchedule();

    if (!schedule.title || !schedule.start || !schedule.end) {
      alert("Por favor completa todos los campos obligatorios.");
      return;
    }

    if (selectedEvent) {
        selectedEvent.setProp("title", schedule.title);
        selectedEvent.setStart(schedule.start);
        selectedEvent.setEnd(schedule.end);
        selectedEvent.setProp("backgroundColor", schedule.color);
        
        update(selectedEvent.id, toScheduleCalendarDto(schedule))
        .then(data=> {
            console.log("Evento actualizado:", data);
            closeModal();
        });
    } 
    else {
        create(toScheduleCalendarDto(schedule))
        .then(data => {
            schedule.id = data ? data.id : null;
            calendar.addEvent(schedule);
            console.log("Evento creado:", data);
            closeModal();
        });
  }
});

function getSchedule(){
    const title = document.getElementById("eventTitle").value.trim();
    const start = document.getElementById("eventStart").value;
    const end = document.getElementById("eventEnd").value;
    const color = document.getElementById("eventColor").value;
    
    return {
        id: selectedEvent ? selectedEvent.id : null,
        title,
        start,
        end,
        color,
        employeeID: 1
    };
}


/**
 * Elimina un scheduleCalendar
 */
const deleteBtn = document.getElementById("deleteEventBtn");

document.getElementById("eventModal").addEventListener("show.bs.modal", () => {
  deleteBtn.classList.toggle("d-none", !selectedEvent);
});

deleteBtn.addEventListener("click", () => {
    if (selectedEvent && confirm("¿Seguro que deseas eliminar este evento?")) {
        console.log(selectedEvent);
        remove(selectedEvent.id)
        .then(res => {
            if (res){
                selectedEvent.remove();
                closeModal();
            }
        })
        .catch(err => console.error("Error:", err)); 
    }
});

/**
 * obtiene todos los scheduleCalendar
 */
document.getElementById("btn-refresh").addEventListener("click", reloadCalendarEvents);

async function reloadCalendarEvents() {
    calendar.removeAllEvents();
    const schedule = await getAll();
    schedule.forEach(ev => calendar.addEvent(ev));
}

/**
 * Otros metodos
 */
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