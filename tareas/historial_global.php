<?php 

require_once __DIR__ . "/../login/Auth.php";
require_once __DIR__ . "/../config/database.php";

Auth::verificar();

$db = Database::conectar();

// 🔥 FILTROS
$buscar = $_GET['buscar'] ?? '';
$desde  = $_GET['desde'] ?? '';
$hasta  = $_GET['hasta'] ?? '';

// 🔥 PAGINACIÓN
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$porPagina = 20;
$offset = ($pagina - 1) * $porPagina;

// 🔥 BASE
$sqlBase = "
    FROM historial_tareas h
    LEFT JOIN usuarios u ON h.usuario_id = u.id
    LEFT JOIN servicios s ON h.servicio_id = s.id
    WHERE 1=1
";

$params = [];

// 🔍 BUSCADOR
if (!empty($buscar)) {
    $sqlBase .= " AND (h.accion LIKE ? OR u.nombre LIKE ? OR s.codigo LIKE ? OR s.cliente LIKE ?)";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
}

// 📅 DESDE
if (!empty($desde)) {
    $sqlBase .= " AND h.fecha >= ?";
    $params[] = $desde . " 00:00:00";
}

// 📅 HASTA
if (!empty($hasta)) {
    $sqlBase .= " AND h.fecha <= ?";
    $params[] = $hasta . " 23:59:59";
}

// 🔢 TOTAL
$stmt = $db->prepare("SELECT COUNT(*) " . $sqlBase);
$stmt->execute($params);
$totalRegistros = $stmt->fetchColumn();
$totalPaginas = ceil($totalRegistros / $porPagina);

// 🔥 DATA
$sql = "
    SELECT h.*, 
           u.nombre as usuario,
           CONCAT(s.codigo, ' - ', s.cliente) as servicio
    $sqlBase
    ORDER BY h.fecha DESC, h.id DESC
    LIMIT $porPagina OFFSET $offset
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =============================
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3">

    <h3>Historial Global</h3>

    <!-- 🔥 EXPORTACIONES -->
    <div class="d-flex gap-2">

        <a href="../export/pdf_historial_global.php?buscar=<?php echo urlencode($buscar); ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>"
           target="_blank"
           class="btn btn-danger btn-sm">
           PDF
        </a>

        <a href="../export/excel_historial_global.php?buscar=<?php echo urlencode($buscar); ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>"
           class="btn btn-success btn-sm">
           Excel
        </a>

        <a href="../export/word_historial_global.php?buscar=<?php echo urlencode($buscar); ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>"
           class="btn btn-primary btn-sm">
           Word
        </a>

    </div>

</div>

<!-- FILTROS -->
<div class="card mb-3">
<div class="card-body">

<form method="GET" class="row g-2">

<div class="col-md-3">
<input type="text" name="buscar" class="form-control" placeholder="Buscar..."
value="<?php echo htmlspecialchars($buscar); ?>">
</div>

<div class="col-md-2">
<input type="date" name="desde" class="form-control"
value="<?php echo htmlspecialchars($desde); ?>">
</div>

<div class="col-md-2">
<input type="date" name="hasta" class="form-control"
value="<?php echo htmlspecialchars($hasta); ?>">
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filtrar</button>
</div>

<div class="col-md-2">
<a href="historial_global.php" class="btn btn-secondary w-100">Limpiar</a>
</div>

</form>

</div>
</div>

<!-- TABLA -->
<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">
<tr>
<th>Tarea</th>
<th>Usuario</th>
<th>Servicio</th>
<th>Detalle</th>
<th>Fecha</th>
</tr>
</thead>

<tbody>

<?php if (!$historial){ ?>
<tr>
<td colspan="5" class="text-center">No hay resultados</td>
</tr>
<?php } ?>

<?php foreach($historial as $h){ ?>

<tr>

<td>#T-<?php echo $h['tarea_id']; ?></td>

<td><?php echo htmlspecialchars($h['usuario'] ?? 'Sistema'); ?></td>

<td>
<?php if(!empty($h['servicio'])){ ?>
<a href="../tareas/ver_servicio.php?id=<?php echo $h['servicio_id']; ?>">
<?php echo htmlspecialchars($h['servicio']); ?>
</a>
<?php } else { ?>
<span class="text-muted">Sin servicio</span>
<?php } ?>
</td>

<td>

<?php
$accion = strip_tags($h['accion']);
$accionSeguro = htmlspecialchars($accion);

$color = 'secondary';

if (stripos($accion, 'creó') !== false) $color = 'success';
elseif (stripos($accion, 'reasignó') !== false || stripos($accion, 'asignó') !== false) $color = 'info';
elseif (stripos($accion, 'cambió') !== false) $color = 'warning';
elseif (stripos($accion, 'eliminó') !== false) $color = 'danger';

echo "<span class='badge bg-$color'>$accionSeguro</span>";
?>

</td>

<td><?php echo date("d/m/Y H:i", strtotime($h['fecha'])); ?></td>

</tr>

<?php } ?>

</tbody>
</table>

</div>
</div>

<!-- PAGINACIÓN -->
<div class="mt-3">

<?php if ($pagina > 1){ ?>
<a class="btn btn-outline-primary btn-sm"
href="?pagina=<?php echo $pagina-1; ?>&buscar=<?php echo urlencode($buscar); ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>">
← Anterior
</a>
<?php } ?>

<?php if ($pagina < $totalPaginas){ ?>
<a class="btn btn-outline-primary btn-sm"
href="?pagina=<?php echo $pagina+1; ?>&buscar=<?php echo urlencode($buscar); ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>">
Siguiente →
</a>
<?php } ?>

<span class="ms-3">
Página <?php echo $pagina; ?> de <?php echo $totalPaginas ?: 1; ?>
</span>

</div>

<a href="/sistema/dashboard/" class="btn btn-secondary mt-3">Volver</a>

<?php
$contenido = ob_get_clean();
require_once __DIR__ . "/../layouts/app.php";
?>