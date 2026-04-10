<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$buscar = $_GET['buscar'] ?? '';
$desde  = $_GET['desde'] ?? '';
$hasta  = $_GET['hasta'] ?? '';

header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=historial_global.doc");

$sqlBase = "
FROM historial_tareas h
LEFT JOIN usuarios u ON h.usuario_id = u.id
LEFT JOIN servicios s ON h.servicio_id = s.id
WHERE 1=1
";

$params = [];

if (!empty($buscar)) {
    $sqlBase .= " AND (h.accion LIKE ? OR u.nombre LIKE ? OR s.codigo LIKE ? OR s.cliente LIKE ?)";
    $params = array_merge($params, ["%$buscar%","%$buscar%","%$buscar%","%$buscar%"]);
}

if (!empty($desde)) {
    $sqlBase .= " AND h.fecha >= ?";
    $params[] = $desde . " 00:00:00";
}

if (!empty($hasta)) {
    $sqlBase .= " AND h.fecha <= ?";
    $params[] = $hasta . " 23:59:59";
}

$sql = "
SELECT h.*, 
       u.nombre as usuario,
       CONCAT(s.codigo, ' - ', s.cliente) as servicio
$sqlBase
ORDER BY h.fecha DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);

echo "<h2>Historial Global</h2>";

echo "<table border='1'>";
echo "<tr>
<th>Tarea</th>
<th>Usuario</th>
<th>Servicio</th>
<th>Detalle</th>
<th>Fecha</th>
</tr>";

while($r = $stmt->fetch()){
echo "<tr>
<td>#T-{$r['tarea_id']}</td>
<td>".($r['usuario'] ?? 'Sistema')."</td>
<td>".($r['servicio'] ?? 'Sin servicio')."</td>
<td>".strip_tags($r['accion'])."</td>
<td>{$r['fecha']}</td>
</tr>";
}

echo "</table>";