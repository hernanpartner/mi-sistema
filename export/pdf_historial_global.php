<?php

ob_start();

require_once "../login/Auth.php";
require_once "../config/database.php";
require_once "../libs/tcpdf/tcpdf.php";

Auth::verificar();

$db = Database::conectar();

// 🔥 FILTROS
$buscar = $_GET['buscar'] ?? '';
$desde  = $_GET['desde'] ?? '';
$hasta  = $_GET['hasta'] ?? '';

$sqlBase = "
FROM historial_tareas h
LEFT JOIN usuarios u ON h.usuario_id = u.id
LEFT JOIN servicios s ON h.servicio_id = s.id
WHERE 1=1
";

$params = [];

if (!empty($buscar)) {
    $sqlBase .= " AND (h.accion LIKE ? OR u.nombre LIKE ? OR s.codigo LIKE ? OR s.cliente LIKE ?)";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
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
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_end_clean();

// =========================
// PDF
// =========================

$pdf = new TCPDF();
$pdf->SetTitle("Historial Global");
$pdf->AddPage();

$html = "<h2>Historial Global</h2>";

$html .= "<table border='1' cellpadding='4'>
<tr style='background:#000;color:#fff;'>
<th><b>Tarea</b></th>
<th><b>Usuario</b></th>
<th><b>Servicio</b></th>
<th><b>Detalle</b></th>
<th><b>Fecha</b></th>
</tr>";

foreach($rows as $r){

$html .= "<tr>
<td>#T-{$r['tarea_id']}</td>
<td>".htmlspecialchars($r['usuario'] ?? 'Sistema')."</td>
<td>".htmlspecialchars($r['servicio'] ?? 'Sin servicio')."</td>
<td>".htmlspecialchars(strip_tags($r['accion']))."</td>
<td>{$r['fecha']}</td>
</tr>";
}

$html .= "</table>";

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("historial_global.pdf","I");
exit;