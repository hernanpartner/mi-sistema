<?php

ob_start(); // 🔥 BLOQUEAR SALIDA

require_once "../login/Auth.php";
require_once "../config/database.php";
require_once "../libs/tcpdf/tcpdf.php";

Auth::verificar();

$db = Database::conectar();

$tarea_id = isset($_GET['tarea']) ? (int)$_GET['tarea'] : 0;

if ($tarea_id <= 0) die("Tarea inválida");

/* =========================
   TAREA
========================= */

$stmt = $db->prepare("SELECT * FROM tareas WHERE id=?");
$stmt->execute([$tarea_id]);
$tarea = $stmt->fetch();

if(!$tarea) die("No existe");

/* =========================
   HISTORIAL
========================= */

$stmt = $db->prepare("
    SELECT h.*, u.nombre 
    FROM historial_tareas h
    LEFT JOIN usuarios u ON h.usuario_id = u.id
    WHERE h.tarea_id = ?
    ORDER BY h.fecha DESC
");
$stmt->execute([$tarea_id]);
$historial = $stmt->fetchAll();

/* =========================
   LIMPIAR OUTPUT
========================= */

ob_end_clean(); // 🔥 LIMPIA TODO antes de PDF

/* =========================
   PDF
========================= */

$pdf = new TCPDF();
$pdf->SetTitle("Historial de Tarea");
$pdf->AddPage();

$html = "
<h2>Historial de Tarea</h2>

<p><b>Título:</b> ".htmlspecialchars($tarea['titulo'])."</p>
<p><b>Servicio ID:</b> {$tarea['servicio_id']}</p>

<hr>

<table border='1' cellpadding='5'>
<tr style='background-color:#333;color:#fff;'>
<th><b>Usuario</b></th>
<th><b>Acción</b></th>
<th><b>Fecha</b></th>
</tr>
";

foreach($historial as $h){

$html .= "
<tr>
<td>".htmlspecialchars($h['nombre'] ?? 'Sistema')."</td>
<td>".htmlspecialchars($h['accion'])."</td>
<td>{$h['fecha']}</td>
</tr>
";
}

$html .= "</table>";

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output("historial_tarea.pdf","I");
exit;