<?php  
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('servicios.ver');

date_default_timezone_set('America/La_Paz');

$db = Database::conectar();
$rol = Auth::rol();

$busqueda = $_GET['q'] ?? '';

if ($busqueda != '') {

    $stmt = $db->prepare("
        SELECT s.*, c.nombre as categoria
        FROM servicios s
        LEFT JOIN categorias c ON s.categoria_id = c.id
        WHERE s.codigo LIKE ? OR s.cliente LIKE ? OR s.descripcion LIKE ?
        ORDER BY s.id DESC
    ");

    $like = "%$busqueda%";
    $stmt->execute([$like, $like, $like]);

} else {

    $stmt = $db->query("
        SELECT s.*, c.nombre as categoria
        FROM servicios s
        LEFT JOIN categorias c ON s.categoria_id = c.id
        ORDER BY s.id DESC
    ");
}

$servicios = $stmt->fetchAll();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
<h3>Servicios</h3>

<div class="d-flex gap-2">

<?php if(Permisos::puede('export.ver')){ ?>
<a href="../export/pdf_servicios.php" target="_blank" class="btn btn-danger btn-sm">PDF</a>
<a href="../export/excel_servicios.php" class="btn btn-success btn-sm">Excel</a>
<a href="../export/word_servicios.php" class="btn btn-primary btn-sm">Word</a>
<?php } ?>

<?php if(Permisos::puede('servicios.crear')){ ?>
<a href="crear_servicio.php" class="btn btn-primary">
<i class="bi bi-plus"></i> Nuevo
</a>
<?php } ?>

</div>
</div>

<form id="formBuscar" class="mb-3">
<div class="input-group">
<input type="text" id="buscarInput" name="q" class="form-control" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
<button class="btn btn-secondary">Buscar</button>
</div>
</form>

<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">
<tr>
<th>Código</th>
<th>Cliente</th>
<th>Origen</th>
<th>Destino</th>
<th>ETD</th>
<th>ETA</th>
<th>Estado</th>
<th>Categoría</th>
<th width="220">Acciones</th>
</tr>
</thead>

<tbody id="tablaServicios">

<?php foreach($servicios as $s){ ?>

<?php
// 🔥 VALIDAR SI TODAS LAS TAREAS ESTÁN TERMINADAS
$stmtT = $db->prepare("
    SELECT COUNT(*) total,
    SUM(CASE WHEN estado='TERMINADO' THEN 1 ELSE 0 END) terminadas
    FROM tareas WHERE servicio_id=?
");
$stmtT->execute([$s['id']]);
$estadoTareas = $stmtT->fetch(PDO::FETCH_ASSOC);

$total = $estadoTareas['total'];
$terminadas = $estadoTareas['terminadas'];

// 🔥 LÓGICA CORRECTA
$completo = ($total > 0 && $total == $terminadas);
?>

<tr id="fila_<?php echo $s['id']; ?>">

<td><?php echo htmlspecialchars($s['codigo']); ?></td>
<td><?php echo htmlspecialchars($s['cliente']); ?></td>
<td><?php echo htmlspecialchars($s['origen']); ?></td>
<td><?php echo htmlspecialchars($s['destino']); ?></td>

<td><?php echo $s['etd'] ? date('d/m H:i', strtotime($s['etd'])) : ''; ?></td>
<td><?php echo $s['eta'] ? date('d/m H:i', strtotime($s['eta'])) : ''; ?></td>

<td id="estado_servicio_<?php echo $s['id']; ?>">

<?php if($s['estado'] === 'FINALIZADO'){ ?>

<span class="badge bg-success">FINALIZADO</span>

<?php } elseif($completo){ ?>

<button onclick="finalizarServicio(<?php echo $s['id']; ?>)" 
class="btn btn-success btn-sm">
✔ Finalizar
</button>

<?php } else { ?>

<span class="badge bg-primary">Activo</span>

<?php } ?>

</td>

<td><?php echo htmlspecialchars($s['categoria']); ?></td>

<td>

<a href="ver_servicio.php?id=<?php echo $s['id']; ?>" class="btn btn-info btn-sm">
<i class="bi bi-eye"></i>
</a>

<?php if(Permisos::puede('servicios.editar')){ ?>
<a href="editar_servicio.php?id=<?php echo $s['id']; ?>" class="btn btn-warning btn-sm">
<i class="bi bi-pencil"></i>
</a>
<?php } ?>

<?php if(Permisos::puede('servicios.eliminar')){ ?>
<button onclick="eliminarServicio(<?php echo $s['id']; ?>)" 
class="btn btn-danger btn-sm">
<i class="bi bi-trash"></i>
</button>
<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>
</div>

<script>

// 🔥 FINALIZAR SERVICIO
function finalizarServicio(id){

if(!confirm('¿Finalizar servicio?')) return;

fetch('finalizar_servicio.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'id='+id
})
.then(r=>r.json())
.then(res=>{
if(res.ok){
document.getElementById('estado_servicio_'+id).innerHTML =
"<span class='badge bg-success'>FINALIZADO</span>";
}else{
alert(res.error || 'Error');
}
});

}

function eliminarServicio(id){
if(!confirm('¿Eliminar servicio?')) return;

fetch('eliminar_servicio.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'id='+id
})
.then(r=>r.json())
.then(res=>{
if(res.ok){
document.getElementById('fila_'+id).remove();
}else{
alert(res.error || 'Error');
}
});
}

</script>

<?php
$contenido = ob_get_clean();
$titulo = "Servicios";
require_once "../layouts/app.php";
?>