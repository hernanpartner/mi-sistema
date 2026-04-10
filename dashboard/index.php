<?php 
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();
$rol = Auth::rol();

$stmt = $db->query("
    SELECT t.titulo, t.fecha_limite, s.codigo
    FROM tareas t
    JOIN servicios s ON t.servicio_id = s.id
    WHERE t.estado != 'terminado'
    AND DATE(t.fecha_limite) < CURDATE()
");
$vencidas = $stmt->fetchAll();

$stmt = $db->query("
    SELECT t.titulo, t.fecha_limite, s.codigo
    FROM tareas t
    JOIN servicios s ON t.servicio_id = s.id
    WHERE t.estado != 'terminado'
    AND DATE(t.fecha_limite) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
");
$por_vencer = $stmt->fetchAll();

ob_start();
?>

<!-- ALERTAS -->
<h5 class="text-danger">🔴 Tareas vencidas (<?php echo count($vencidas); ?>)</h5>
<ul>
<?php foreach($vencidas as $v){ ?>
<li class="text-danger">[<?php echo $v['codigo']; ?>] <?php echo $v['titulo']; ?></li>
<?php } ?>
</ul>

<h5 class="text-warning">🟡 Tareas por vencer (<?php echo count($por_vencer); ?>)</h5>
<ul>
<?php foreach($por_vencer as $p){ ?>
<li class="text-warning">[<?php echo $p['codigo']; ?>] <?php echo $p['titulo']; ?></li>
<?php } ?>
</ul>

<hr>

<!-- TARJETAS -->
<div class="row">

<div class="col-lg-2 col-6"><div class="small-box text-bg-warning"><div class="inner"><h3 id="pendientes">0</h3><p>Pendientes</p></div></div></div>
<div class="col-lg-2 col-6"><div class="small-box text-bg-info"><div class="inner"><h3 id="proceso">0</h3><p>Proceso</p></div></div></div>
<div class="col-lg-2 col-6"><div class="small-box text-bg-danger"><div class="inner"><h3 id="bloqueadas">0</h3><p>Bloqueadas</p></div></div></div>
<div class="col-lg-2 col-6"><div class="small-box text-bg-success"><div class="inner"><h3 id="terminadas">0</h3><p>Terminadas</p></div></div></div>
<div class="col-lg-2 col-6"><div class="small-box text-bg-dark"><div class="inner"><h3 id="atrasadas">0</h3><p>Atrasadas</p></div></div></div>

</div>

<!-- GRAFICO -->
<div class="card mt-4">
<div class="card-header">Resumen</div>
<div class="card-body">
<canvas id="grafico"></canvas>
</div>
</div>

<script>
let chart;

function cargarDashboard() {
fetch('/sistema/api/dashboard.php')
.then(r => r.json())
.then(d => {

document.getElementById('pendientes').innerText = d.pendientes || 0;
document.getElementById('proceso').innerText = d.proceso || 0;
document.getElementById('bloqueadas').innerText = d.bloqueadas || 0;
document.getElementById('terminadas').innerText = d.terminadas || 0;
document.getElementById('atrasadas').innerText = d.atrasadas || 0;

if(chart) chart.destroy();

chart = new Chart(document.getElementById('grafico'), {
type: 'bar',
data: {
labels: ['Pendientes','Proceso','Bloqueadas','Terminadas','Atrasadas'],
datasets: [{
label: 'Tareas',
data: [
Number(d.pendientes),
Number(d.proceso),
Number(d.bloqueadas),
Number(d.terminadas),
Number(d.atrasadas)
]
}]
}
});

});
}

// 🔥 IMPORTANTE (esto arregla TODO)
function cargarNotificaciones(){

fetch('/sistema/api/notificaciones.php')
.then(r => r.json())
.then(data=>{

let noLeidas = data.filter(n => n.leido == 0).length;
document.getElementById('notif-count').innerText = noLeidas;

let html = '';

if(data.length === 0){
html = '<span class="dropdown-item">Sin notificaciones</span>';
}else{

data.forEach(n=>{
html += `
<a href="${n.link}" 
onclick="marcarLeida(${n.id})"
class="dropdown-item ${n.leido == 0 ? 'bg-light' : ''}">
${n.mensaje}
</a>`;
});

}

document.getElementById('notif-list').innerHTML = html;

});
}

function marcarLeida(id){
fetch('/sistema/notificaciones/marcar_leidas.php', {
method: 'POST',
headers: {'Content-Type': 'application/x-www-form-urlencoded'},
body: new URLSearchParams({ id: id })
});
}

cargarDashboard();
cargarNotificaciones();

setInterval(cargarDashboard, 10000);
setInterval(cargarNotificaciones, 10000);
</script>

<?php
$contenido = ob_get_clean();
$titulo = "Dashboard";

require_once "../layouts/app.php";