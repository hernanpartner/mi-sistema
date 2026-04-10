<?php
require_once __DIR__ . "/../login/Auth.php";
require_once __DIR__ . "/../config/database.php";

Auth::verificar();

$db = Database::conectar();

$tarea_id = isset($_GET['tarea']) ? (int)$_GET['tarea'] : 0;

if ($tarea_id <= 0) {
    die("Tarea inválida");
}

// Obtener tarea
$stmt = $db->prepare("SELECT * FROM tareas WHERE id = ?");
$stmt->execute([$tarea_id]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    die("Tarea no encontrada");
}

// 🔥 HISTORIAL REAL
$stmt = $db->prepare("
    SELECT h.*, u.nombre 
    FROM historial_tareas h
    LEFT JOIN usuarios u ON h.usuario_id = u.id
    WHERE h.tarea_id = ?
    ORDER BY h.fecha DESC
");
$stmt->execute([$tarea_id]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =============================
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3">

<h3>Historial de Tarea</h3>

<!-- 🔥 EXPORTACIONES -->
<div class="d-flex gap-2">

<a href="../export/pdf_historial_tarea.php?tarea=<?php echo $tarea_id; ?>" target="_blank" class="btn btn-danger btn-sm">
PDF
</a>

<a href="../export/excel_historial_tarea.php?tarea=<?php echo $tarea_id; ?>" class="btn btn-success btn-sm">
Excel
</a>

<a href="../export/word_historial_tarea.php?tarea=<?php echo $tarea_id; ?>" class="btn btn-primary btn-sm">
Word
</a>

</div>

</div>

<div class="card">
<div class="card-body">

<p><strong>Título:</strong> <?php echo htmlspecialchars($tarea['titulo']); ?></p>

<hr>

<?php if(!$historial){ ?>
<p>No hay historial</p>
<?php } ?>

<ul class="list-group">

<?php foreach($historial as $h){ ?>

<li class="list-group-item">

<strong><?php echo htmlspecialchars($h['nombre'] ?? 'Sistema'); ?></strong><br>

<?php echo htmlspecialchars($h['accion']); ?>

<br>
<small class="text-muted"><?php echo $h['fecha']; ?></small>

</li>

<?php } ?>

</ul>

</div>
</div>

<a href="ver_servicio.php?id=<?php echo $tarea['servicio_id']; ?>" class="btn btn-secondary mt-3">
⬅ Volver
</a>

<?php
$contenido = ob_get_clean();
require_once __DIR__ . "/../layouts/app.php";
?>