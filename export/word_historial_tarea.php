<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$tarea_id = (int)$_GET['tarea'];

header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=historial_tarea.doc");

/* ========================= */

$stmt = $db->prepare("
    SELECT h.*, u.nombre 
    FROM historial_tareas h
    LEFT JOIN usuarios u ON h.usuario_id = u.id
    WHERE h.tarea_id = ?
    ORDER BY h.fecha DESC
");
$stmt->execute([$tarea_id]);

echo "<h2>Historial de Tarea</h2>";

echo "<table border='1'>";
echo "<tr>
<th>Usuario</th>
<th>Acción</th>
<th>Fecha</th>
</tr>";

while($h = $stmt->fetch()){

echo "<tr>
<td>".($h['nombre'] ?? 'Sistema')."</td>
<td>{$h['accion']}</td>
<td>{$h['fecha']}</td>
</tr>";
}

echo "</table>";