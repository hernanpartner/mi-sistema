<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$servicio_id = (int)$_GET['servicio'];

header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=tareas.doc");

/* ========================= */

$stmt = $db->prepare("
    SELECT t.*, u.nombre as responsable
    FROM tareas t
    LEFT JOIN usuarios u ON t.responsable_id = u.id
    WHERE t.servicio_id = ?
");
$stmt->execute([$servicio_id]);

echo "<h2>Reporte de Tareas</h2>";

echo "<table border='1'>";
echo "<tr>
<th>Título</th>
<th>Descripción</th>
<th>Responsable</th>
<th>Prioridad</th>
<th>Estado</th>
<th>Fecha</th>
</tr>";

while($t = $stmt->fetch()){

echo "<tr>
<td>{$t['titulo']}</td>
<td>{$t['descripcion']}</td>
<td>{$t['responsable']}</td>
<td>{$t['prioridad']}</td>
<td>{$t['estado']}</td>
<td>{$t['fecha_limite']}</td>
</tr>";
}

echo "</table>";