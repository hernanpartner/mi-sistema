<?php
require_once "../login/Auth.php";
Auth::verificar();

$titulo = "Calendario de Tareas";

ob_start();
?>

<link href="/sistema/libs/fullcalendar/index.global.min.css" rel="stylesheet">
<script src="/sistema/libs/fullcalendar/index.global.min.js"></script>

<style>
.tooltip-custom {
    position: fixed;
    background: #343a40;
    color: #fff;
    padding: 8px;
    border-radius: 5px;
    font-size: 12px;
    display: none;
    z-index: 9999;
}

.modal-custom {
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.5);
    display:none;
    z-index:10000;
}

.modal-content-custom {
    background:#fff;
    width:600px;
    max-width:90%;
    margin:50px auto;
    padding:20px;
    border-radius:8px;
}
</style>

<div class="card">
<div class="card-header">
<h3 class="card-title">📅 Calendario de Tareas</h3>
</div>

<div class="card-body">

<div id="calendar"></div>
<div id="tooltip" class="tooltip-custom"></div>

</div>
</div>

<!-- MODAL -->
<div id="modalTarea" class="modal-custom">
    <div class="modal-content-custom">
        <button onclick="cerrarModal()" class="btn btn-danger btn-sm float-end">X</button>
        <div id="contenidoModal"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

let tooltip = document.getElementById('tooltip');
let calendarEl = document.getElementById('calendar');

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
        .then(data => successCallback(data))
        .catch(err => failureCallback(err));

    },

    eventDidMount: function(info) {

        info.el.addEventListener('mousemove', function(e) {

            let props = info.event.extendedProps;

            tooltip.innerHTML =
            "<b>" + info.event.title + "</b><br>" +
            "Estado: " + props.estado + "<br>" +
            "Prioridad: " + props.prioridad + "<br>" +
            "Responsable: " + (props.responsable ?? '-') + "<br>" +
            "Fecha: " + props.fecha;

            tooltip.style.top = (e.clientY + 15) + "px";
            tooltip.style.left = (e.clientX + 15) + "px";
            tooltip.style.display = 'block';
        });

        info.el.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });

    },

    eventClick: function(info) {

        info.jsEvent.preventDefault();

        let tarea_id = info.event.id;

        abrirModal(tarea_id);
    }

});

calendar.render();

});

// 🔥 MODAL AJAX
function abrirModal(id){

    fetch('/sistema/tareas/ver_tarea_modal.php?id='+id)
    .then(r => r.text())
    .then(html => {

        document.getElementById('contenidoModal').innerHTML = html;
        document.getElementById('modalTarea').style.display = 'block';

    });

}

function cerrarModal(){
    document.getElementById('modalTarea').style.display = 'none';
}
</script>

<?php
$contenido = ob_get_clean();
require_once "../layouts/app.php";
?>