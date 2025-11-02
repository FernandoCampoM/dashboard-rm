import { getAll, remove, create, update } from "./services/scheduleService.js";
import { getAllUsers } from "./services/userService.js"; 
import { getAllAvailableSchedule, createAvailableSchedule, updateAvailableSchedule, removeAvailableSchedule } from "./services/availableSchedule.js";
import { toScheduleCalendarDto } from "./factory/scheduleCalendarFactory.js";
import { toAvailableScheduleDto } from "./factory/availableScheduleFactory.js";

/* global FullCalendar */
let calendar;
let selectedEvent = null; 
let listUsers = null;
/**
 * Calendar
 */
document.addEventListener("DOMContentLoaded", function () {
    getAllUsers()
    .then(users => {
        listUsers = users;
        console.log("Users: ",listUsers);
        loadCalendar();
    });
    
});

function loadCalendar(){
    
    reloadAvailableSchedule();
    
     // Agregar un nuevo horario
    document.getElementById("btn-add").addEventListener("click", () => {
        abrirModalHorario();
    });
    
    let containerEl = document.getElementById('external-events');
    new FullCalendar.Draggable(containerEl, {
        itemSelector: '.fc-event',
        eventData: function (eventEl) {
            return {
                title: eventEl.getAttribute('data-title'),
                duration: eventEl.getAttribute('data-duration'),
                extendedProps: {
                  employeeID: eventEl.getAttribute('data-employee')
                },
                color: eventEl.getAttribute('data-color')
            };
        }
    });
    
    var calendarEl = document.getElementById("calendar");
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek', 
        slotLabelFormat: {
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true
        },
        locale: 'es',
        selectable: true,
        editable: true,
        expandRows: true,
        droppable: true,
        allDaySlot: false,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        events: [],
        eventContent: function(arg) {
            // Creamos elementos HTML personalizados
            const titleEl = document.createElement('div');
            titleEl.textContent = arg.event.title;

            const userEl = document.createElement('div');
            userEl.textContent = getNameUser(arg.event.extendedProps.employeeID);
            userEl.style.fontSize = '0.85em';
            userEl.style.color = '#fff';
            userEl.style.opacity = '0.9';

            // Retornamos ambos
            return { domNodes: [titleEl, userEl] };
        },
        select: function (info) {
            cargarListaEmpleados2();
            document.getElementById("employeeID2").value = "";
            
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
            cargarListaEmpleados2();
            selectedEvent = info.event; // Guardamos el evento seleccionado
            // Precargamos datos en el formulario
            document.getElementById("eventTitle").value = selectedEvent.title;
            document.getElementById("eventStart").value = selectedEvent.startStr.slice(0, 16);
            document.getElementById("eventEnd").value = selectedEvent.endStr ? selectedEvent.endStr.slice(0, 16) : "";
            document.getElementById("eventColor").value = selectedEvent.backgroundColor || "#1e90ff";
            document.getElementById("employeeID2").value = selectedEvent.extendedProps.employeeID;

            // Abrimos el modal
            const modalElement = document.getElementById("eventModal");
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
        },
        
        eventDrop: function (info) {
            const event = info.event;
            
            // Datos actualizados
            const updatedEvent = {
              id: event.id,
              title: event.title,
              dateStart: event.startStr.slice(0, 19).replace("T", " "),
              dateEnd: event.endStr ? event.endStr.slice(0, 19).replace("T", " ") : "",
              color: event.backgroundColor || "#1e90ff",
              employeeID: event.extendedProps.employeeID
            };

            console.log("Evento movido:", updatedEvent);
            
            update(event.id, updatedEvent)
            .then((data)=>{
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
              employeeID: event.extendedProps.employeeID
            };

            update(event.id, updatedEvent)
            .then((data)=>{
                console.log("Evento actualizado en backend:", data);
            })
            .catch(err => {
                console.error("Error al actualizar:", err);
                alert("No se pudo actualizar el evento. Se revertirá el cambio.");
                info.revert(); // 👈 Revierte el cambio en el calendario
            });
        },
        
        eventReceive: function(info) {
            let event = info.event;
            
            
            const createEvent = {
                title: event.title,
                dateStart: event.startStr.slice(0, 19).replace("T", " "),
                dateEnd: event.endStr.slice(0, 19).replace("T", " "),
                color: event.backgroundColor || "#1e90ff",
                employeeID: event.extendedProps.employeeID
            };
            console.log(createEvent);
            
            create(createEvent)
            .then((data)=>{
                console.log("Evento creado en backend:", data);
                reloadCalendarEvents();
            })
            .catch(err => {
                console.error("Error al actualizar:", err);
                alert("No se pudo actualizar el evento. Se revertirá el cambio.");
                info.revert(); // 👈 Revierte el cambio en el calendario
            });
        }
    });

    calendar.render();
    reloadCalendarEvents();
}


function reloadAvailableSchedule(){
    getAllAvailableSchedule()
    .then(horarios => {
        console.log("Horario: ",horarios);
        const container = document.getElementById("external-events");
        container.innerHTML = ""; // limpiar contenedor
        horarios.forEach(h => {
            addExternalEvents(h);
        });
    });
    
    const colors = [
        { name: 'Azul principal', value: '#0d6efd' },
        { name: 'Azul cielo', value: '#357cd2' },
        { name: 'Verde esmeralda', value: '#1ca955' },
        { name: 'Verde oliva', value: '#7fa900' },
        { name: 'Naranja brillante', value: '#f57f16' },
        { name: 'Turquesa', value: '#03bdae' },
        { name: 'Rojo coral', value: '#ea7a57' }
    ];
    
    const select = document.getElementById('color');
    
    select.innerHTML = '<option value="">Seleccione un color</option>';

    colors.forEach(color => {
        const option = document.createElement('option');
        option.value = color.value;
        option.textContent = color.name;
        option.style.backgroundColor = color.value;
        option.style.color = getContrastColor(color.value);
        select.appendChild(option);
    });

    select.addEventListener('change', () => {
        const color = select.value || '#ced4da'; // color por defecto (Bootstrap)
        select.style.borderColor = color;
        select.style.boxShadow = color === '#ced4da' ? '' : `0 0 0 0.2rem ${color}40`;
    });
  
}

// Función para calcular color de texto según fondo (mejor contraste)
function getContrastColor(hex) {
    const r = parseInt(hex.substr(1, 2), 16);
    const g = parseInt(hex.substr(3, 2), 16);
    const b = parseInt(hex.substr(5, 2), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 128 ? 'black' : 'white';
}

function addExternalEvents(h){
    const container = document.getElementById("external-events");

    const div = document.createElement("div");
    div.classList.add("fc-event", "border", "p-2", "mb-2", "rounded");
    div.textContent = getNameUser(h.employeeID);
    div.setAttribute("data-id", h.id);
    div.setAttribute("data-title", h.title);
    div.setAttribute("data-duration", h.duration);
    div.setAttribute("data-employee", h.employeeID);
    div.setAttribute("data-color", h.color);
    
    if (h.color) {
        div.style.backgroundColor = h.color;
        div.style.color = getContrastColor(h.color); // texto legible
        div.style.borderColor = h.color; // opcional: borde del mismo tono
    }
    
    div.style.cursor = "pointer";

    // Click: abrir modal de edición
    div.addEventListener("click", () => editarHorario(h));

    container.appendChild(div);
}

function getNameUser(id){
    if (listUsers){
        for (var user of listUsers) {
            if (user.id === id){
                return user.name;
            }
        }
    }
    return "";
}

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

    if (!schedule.title || !schedule.start || !schedule.end || !schedule.employeeID) {
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
    const employeeID = document.getElementById("employeeID2").value;
    
    return {
        id: selectedEvent ? selectedEvent.id : null,
        title,
        start,
        end,
        color,
        employeeID: employeeID
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

function cargarListaEmpleados() {
  const select = document.getElementById("employeeID");
  select.innerHTML = '<option value="">Seleccione un empleado</option>';

  listUsers.forEach(user => {
    const option = document.createElement("option");
    option.value = user.id;
    option.textContent = user.name;
    select.appendChild(option);
  });
}

function cargarListaEmpleados2() {
  const select = document.getElementById("employeeID2");
  select.innerHTML = '<option value="">Seleccione un empleado</option>';

  listUsers.forEach(user => {
    const option = document.createElement("option");
    option.value = user.id;
    option.textContent = user.name;
    select.appendChild(option);
  });
}


function abrirModalHorario(horario = null) {
    cargarListaEmpleados();
    const modal = new bootstrap.Modal(document.getElementById("modalHorario"));
    document.getElementById("formHorario").reset();
    document.getElementById("asId").value = horario?.asId || "";
    document.getElementById("title").value = horario?.title || "";
    document.getElementById("duration").value = horario?.duration || "";
    document.getElementById("employeeID").value = horario?.employeeID || "";
    document.getElementById("color").value = horario?.color || "";
    document.getElementById("btn-delete").style.display = horario ? "inline-block" : "none";
    modal.show();

    // Guardar o actualizar
    document.getElementById("btn-save").onclick = () => guardarHorario(horario);
    // Eliminar
    document.getElementById("btn-delete").onclick = () => eliminarHorario(horario.id);
}

function editarHorario(h) {
  abrirModalHorario(h);
}

// Guardar o actualizar
function guardarHorario(horario) {
    const datos = {
      asId: document.getElementById("asId").value,
      title: document.getElementById("title").value,
      duration: document.getElementById("duration").value,
      employeeID: document.getElementById("employeeID").value,
      color: document.getElementById("color").value
    };
    
    if (!datos.title || !datos.duration || !datos.employeeID || !datos.color) {
      alert("Por favor completa todos los campos obligatorios.");
      return;
    }
    
    if (horario){
        console.log("horario edit: ", horario);
        console.log("datos: ", datos);
        updateAvailableSchedule(horario.id, datos)
        .then(() => {
            reloadAvailableSchedule();
            closeModalAvailableSchedule(); 
        });
    }else{
        console.log("horario save: ", horario);
        console.log("datos: ", datos);
        createAvailableSchedule(datos)
        .then(() => {
            reloadAvailableSchedule();
            closeModalAvailableSchedule();
        });
    }
}

// Eliminar
function eliminarHorario(id) {
    if (!confirm("¿Deseas eliminar este horario?")) return;
    removeAvailableSchedule(id)
    .then(() => {
        reloadAvailableSchedule();
        closeModalAvailableSchedule();
    });
}


function closeModalAvailableSchedule() {
  const modalElement = document.getElementById("modalHorario");
  const modalInstance = bootstrap.Modal.getInstance(modalElement);
  modalInstance.hide();

  document.body.classList.remove("modal-open");
  const backdrops = document.getElementsByClassName("modal-backdrop");
  while (backdrops.length > 0) {
    backdrops[0].parentNode.removeChild(backdrops[0]);
  }

  selectedEvent = null; // Limpiamos el estado
}