/* global FullCalendar */


let calendar;

document.addEventListener("DOMContentLoaded", function () {
    var calendarEl = document.getElementById("calendar");
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek', // vista semanal con horas
        locale: 'es',
        selectable: true,
        editable: true,
        expandRows: true,
        allDaySlot: false, // 🔹 Quita la fila "All-day"
        slotMinTime: "07:00:00", // 🔹 inicia a las 7:00 AM
        slotMaxTime: "22:00:00",
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        events: [
          {
            id: '1',
            title: 'Clase Matemáticas',
            start: '2025-09-28T08:00:00',
            end: '2025-09-28T10:00:00'
          },
          {
            id: '2',
            title: 'Reunión Proyecto',
            start: '2025-09-29T14:00:00',
            end: '2025-09-29T16:00:00'
          },
          {
            id: '3',
            title: 'Entrenamiento',
            start: '2025-09-30T18:00:00',
            end: '2025-09-30T20:00:00'
          }
        ],

        select: function (info) {
          let title = prompt('Nombre de la actividad:');
          if (title) {
            calendar.addEvent({
              title: title,
              start: info.startStr,
              end: info.endStr,
              allDay: false // 🔹 Forzamos a que nunca sea "All-day"
            });
          }
          calendar.unselect();
        },

        eventClick: function (info) {
          if (confirm("¿Eliminar este evento?")) {
            info.event.remove();
          }
        }
    });

  calendar.render();
});

// Cuando se quite la clase "d-none" del horario-section
function showHorario() {
  const section = document.getElementById("horario-section");
  section.classList.remove("d-none");

  // 🔥 Forzar recalcular tamaño del calendario
  setTimeout(() => {
    calendar.updateSize();
    calendar.changeView("timeGridWeek"); // opcional, asegurar vista semanal
  }, 50);
}

// Ejemplo: al hacer clic en el menú de horario
document.getElementById("horario-link").addEventListener("click", function () {
  showHorario();
});
