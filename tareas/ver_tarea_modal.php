<?php
require_once "../config/database.php";
require_once "../login/Auth.php";

Auth::verificar();

$db = Database::conectar();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("
    SELECT t.*, u.nombre as responsable, s.codigo
    FROM tareas t
    LEFT JOIN usuarios u ON t.responsable_id = u.id
    LEFT JOIN servicios s ON t.servicio_id = s.id
    WHERE t.id = ?
");

$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$t){
    echo "Tarea no encontrada";
    exit;
}
?>

<h4>[<?php echo $t['codigo']; ?>] <?php echo htmlspecialchars($t['titulo']); ?></h4>

<p><b>Descripción:</b><br><?php echo nl2br(htmlspecialchars($t['descripcion'])); ?></p>

<p><b>Responsable:</b> <?php echo htmlspecialchars($t['responsable'] ?? 'Sin asignar'); ?></p>

<p><b>Estado:</b> <?php echo $t['estado']; ?></p>

<p><b>Prioridad:</b> <?php echo $t['prioridad']; ?></p>

<p><b>Fecha límite:</b> <?php echo $t['fecha_limite']; ?></p>

<hr>

<a href="ver_servicio.php?id=<?php echo $t['servicio_id']; ?>&tarea=<?php echo $t['id']; ?>" class="btn btn-primary">
Ver completo
</a>