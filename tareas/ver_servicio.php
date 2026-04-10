<?php

require_once __DIR__ . "/../login/Auth.php";
require_once __DIR__ . "/../login/Permisos.php";
require_once __DIR__ . "/../config/database.php";

Auth::verificar();
Permisos::requerir('tareas.ver');

$db = Database::conectar();

$servicio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tarea_resaltar = isset($_GET['tarea']) ? (int)$_GET['tarea'] : 0;

if ($servicio_id <= 0) {
    header("Location: index.php");
    exit;
}

/* =========================
   MARCAR NOTIFICACION
========================= */
if ($tarea_resaltar > 0) {
    $stmt = $db->prepare("
        UPDATE notificaciones 
        SET leido = 1 
        WHERE tarea_id = ? 
        AND usuario_id = ?
    ");
    $stmt->execute([
        $tarea_resaltar,
        $_SESSION['usuario_id']
    ]);
}

/* =========================
   SERVICIO
========================= */
$stmt = $db->prepare("SELECT * FROM servicios WHERE id = ?");
$stmt->execute([$servicio_id]);
$servicio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$servicio) {
    header("Location: index.php");
    exit;
}

/* =========================
   TAREAS
========================= */
$stmt = $db->prepare("
    SELECT t.*, u.nombre as responsable
    FROM tareas t
    LEFT JOIN usuarios u ON t.responsable_id = u.id
    WHERE t.servicio_id = ?
    ORDER BY t.id DESC
");
$stmt->execute([$servicio_id]);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Servicio: <?php echo htmlspecialchars($servicio['codigo']); ?></h3>

    <div class="d-flex gap-2">

        <?php if(Permisos::puede('export.ver')){ ?>
        <a href="../export/pdf_tareas.php?servicio=<?php echo $servicio_id; ?>" target="_blank" class="btn btn-danger btn-sm">PDF</a>
        <a href="../export/excel_tareas.php?servicio=<?php echo $servicio_id; ?>" class="btn btn-success btn-sm">Excel</a>
        <a href="../export/word_tareas.php?servicio=<?php echo $servicio_id; ?>" class="btn btn-primary btn-sm">Word</a>
        <?php } ?>

        <?php if(Permisos::puede('tareas.crear')){ ?>
        <a href="crear_tarea.php?servicio=<?php echo $servicio_id; ?>" class="btn btn-success">
            <i class="bi bi-plus"></i> Nueva tarea
        </a>
        <?php } ?>

    </div>
</div>

<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">
<tr>
<th>Título</th>
<th>Descripción</th>
<th>Responsable</th>
<th>Prioridad</th>
<th>Estado</th>
<th>Fecha</th>
<th width="280">Acciones</th>
</tr>
</thead>

<tbody>

<?php if (!$tareas){ ?>
<tr>
<td colspan="7" class="text-center">No hay tareas registradas</td>
</tr>
<?php } ?>

<?php foreach($tareas as $t){ ?>

<?php
$highlight = ($t['id'] == $tarea_resaltar) ? "table-warning" : "";
$esResponsable = ($t['responsable_id'] == $_SESSION['usuario_id']);
?>

<tr class="<?php echo $highlight; ?>" id="tarea_<?php echo $t['id']; ?>">

<td><?php echo htmlspecialchars($t['titulo']); ?></td>
<td><?php echo htmlspecialchars($t['descripcion']); ?></td>

<td><?php echo htmlspecialchars($t['responsable'] ?? 'Sin asignar'); ?></td>

<td>
<?php
$colorP = 'secondary';
if($t['prioridad'] == 'URGENTE') $colorP = 'danger';
elseif($t['prioridad'] == 'ALTA') $colorP = 'warning';
elseif($t['prioridad'] == 'MEDIA') $colorP = 'info';

echo "<span class='badge bg-$colorP'>{$t['prioridad']}</span>";
?>
</td>

<td>
<?php
$colorE = 'secondary';
if($t['estado'] == 'EN PROCESO') $colorE = 'info';
elseif($t['estado'] == 'BLOQUEADO') $colorE = 'warning';
elseif($t['estado'] == 'TERMINADO') $colorE = 'success';

echo "<span class='badge bg-$colorE'>{$t['estado']}</span>";
?>
</td>

<td><?php echo $t['fecha_limite']; ?></td>

<td>

<?php if ($esResponsable && Permisos::puede('tareas.cambiar_estado')) { ?>

    <?php if ($t['estado'] != 'TERMINADO') { ?>

        <button onclick="cambiarEstado(<?php echo $t['id']; ?>,'EN PROCESO')" class="btn btn-info btn-sm">▶</button>
        <button onclick="cambiarEstado(<?php echo $t['id']; ?>,'BLOQUEADO')" class="btn btn-warning btn-sm">⛔</button>
        <button onclick="cambiarEstado(<?php echo $t['id']; ?>,'TERMINADO')" class="btn btn-success btn-sm">✔</button>

    <?php } else { ?>

        <span class="badge bg-success">FINALIZADO</span>

    <?php } ?>

<?php } ?>

<?php if(Permisos::puede('tareas.historial')){ ?>
<a href="historial_tarea.php?tarea=<?php echo $t['id']; ?>" class="btn btn-dark btn-sm mt-1">
Historial
</a>
<?php } ?>

<?php if ($esResponsable && $t['estado'] != 'TERMINADO') { ?>
<a href="reasignar_tarea.php?tarea=<?php echo $t['id']; ?>&servicio=<?php echo $servicio_id; ?>"
class="btn btn-secondary btn-sm mt-1">
🔄 Reasignar
</a>
<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>
</table>

</div>
</div>

<a href="index.php" class="btn btn-secondary mt-3">⬅ Volver</a>

<script>
async function cambiarEstado(id, estado){
try{
let res = await fetch('cambiar_estado.php',{
method:'POST',
headers:{'Content-Type':'application/json'},
body: JSON.stringify({id, estado})
});
let data = await res.json();
if(data.ok){
location.reload();
}else{
alert(data.error || 'Error');
}
}catch(e){
alert('Error de conexión');
}
}

// 🔥 SCROLL + REFRESCO NOTIFICACIONES
document.addEventListener('DOMContentLoaded', function(){

    let tareaId = "<?php echo $tarea_resaltar; ?>";

    if(tareaId){

        let fila = document.getElementById('tarea_' + tareaId);

        if(fila){
            fila.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

    }

    // 🔥 FORZAR ACTUALIZACIÓN DE NOTIFICACIONES
    if(typeof cargarNotificaciones === "function"){
        cargarNotificaciones();
    }

});
</script>

<?php
$contenido = ob_get_clean();
require_once __DIR__ . "/../layouts/app.php";
?>