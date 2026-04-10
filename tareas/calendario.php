<?php
require_once "../login/Auth.php";
Auth::verificar();

$titulo = "Calendario de Tareas";

ob_start();
?>

<link href="/sistema/libs/fullcalendar/index.global.min.css" rel="stylesheet">
<script src="/sistema/libs/fullcalendar/index.global.min.js"></script>

<!-- 🔴 COMENTA ESTO SI NO EXISTE BIEN -->
<!-- <script src="/sistema/libs/fullcalendar/multimonth.global.min.js"></script> -->

<style>
.tooltip-custom {
    position: absolute;
    background: #343a40;
    color: #fff;
    padding: 8px;
    border-radius: 5px;
    font-size: 12px;
    display: none;
    z-index: 9999;
}
</style>

<div class="card">
<div class="card-header">
<h3 class="card-title">📅 Calendario de Tareas</h3>
</div>

<div class="card-body">

<div class="row mb-3">

<div class="col-md-3">
<label>Año</label>
<select id="yearSelect" class="form-control"></select>
</div>

<div class="col-md-9">

<b>Estado:</b><br>

<label><input type="checkbox" class="filtro-estado" value="PENDIENTE" checked> Pendiente</label>
<label><input type="checkbox" class="filtro-estado" value="EN PROCESO" checked> En proceso</label>
<label><input type="checkbox" class="filtro-estado" value="BLOQUEADO" checked> Bloqueado</label>
<label><input type="checkbox" class="filtro-estado" value="TERMINADO" checked> Terminado</label>

<br><br>

<b>Prioridad:</b><br>

<label><input type="checkbox" class="filtro-prioridad" value="BAJA" checked> Baja</label>
<label><input type="checkbox" class="filtro-prioridad" value="MEDIA" checked> Media</label>
<label><input type="checkbox" class="filtro-prioridad" value="ALTA" checked> Alta</label>
<label><input type="checkbox" class="filtro-prioridad" value="URGENTE" checked> Urgente</label>

</div>

</div>

<div id="calendar"></div>
<div id="tooltip" class="tooltip-custom"></div>

</div>
</div>

<a href="/sistema/dashboard/" class="btn btn-secondary mt-3">⬅ Volver</a>

<script>
document.addEventListener('DOMContentLoaded', function() {

let tooltip = document.getElementById('tooltip');
let calendarEl = document.getElementById('calendar');
let yearSelect = document.getElementById('yearSelect');

let currentYear = new Date().getFullYear();

// años
for (let y = currentYear - 5; y <= currentYear + 5; y++) {
    let option = document.createElement("option");
    option.value = y;
    option.text = y;
    if (y === currentYear) option.selected = true;
    yearSelect.appendChild(option);
}

// filtros
function getEstados(){
    return Array.from(document.querySelectorAll('.filtro-estado:checked')).map(e => e.value);
}

function getPrioridades(){
    return Array.from(document.querySelectorAll('.filtro-prioridad:checked')).map(e => e.value);
}

let calendar = new FullCalendar.Calendar(calendarEl, {

initialView: 'dayGridMonth',
locale: 'es',

headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
},

events: function(fetchInfo, successCallback, failureCallback){

    fetch('/sistema/tareas/eventos.php')
    .then(r => r.json())
    .then(data => {

        let estados = getEstados();
        let prioridades = getPrioridades();

        let filtrados = data.filter(e => {
            return estados.includes(e.extendedProps.estado) &&
                   prioridades.includes(e.extendedProps.prioridad);
        });

        successCallback(filtrados);

    })
    .catch(err => {
        console.error(err);
        failureCallback(err);
    });

},

// 🔥 TOOLTIP PROFESIONAL (ESTABLE)
eventDidMount: function(info) {

    let tooltip = document.getElementById('tooltip');

    info.el.addEventListener('mouseenter', function(e) {

        let props = info.event.extendedProps;

        tooltip.innerHTML =
        "<b>" + info.event.title + "</b><br>" +
        "Estado: " + props.estado + "<br>" +
        "Prioridad: " + props.prioridad + "<br>" +
        "Responsable: " + (props.responsable ?? '-') + "<br>" +
        "Fecha: " + props.fecha;

        tooltip.style.display = 'block';
    });

    info.el.addEventListener('mouseleave', function() {
        tooltip.style.display = 'none';
    });

},

eventClick: function(info) {

    info.jsEvent.preventDefault();

    let servicio_id = info.event.extendedProps.servicio_id;

    if(servicio_id){
        window.location.href = '/sistema/tareas/ver_servicio.php?id=' + servicio_id + '&highlight=' + info.event.id;
    }
}

});

calendar.render();

// filtros
document.querySelectorAll('.filtro-estado, .filtro-prioridad').forEach(cb => {
    cb.addEventListener('change', function() {
        calendar.refetchEvents();
    });
});

// año
yearSelect.addEventListener('change', function() {
    calendar.gotoDate(this.value + "-01-01");
});

// tooltip
document.addEventListener('mousemove', function(e) {
    tooltip.style.top = (e.pageY + 10) + "px";
    tooltip.style.left = (e.pageX + 10) + "px";
});

});
</script>

<?php
$contenido = ob_get_clean();
require_once "../layouts/app.php";
?>