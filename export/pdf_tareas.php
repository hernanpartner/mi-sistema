<?php

require_once "../login/Auth.php";
require_once "../config/database.php";
require_once "../libs/tcpdf/tcpdf.php";

Auth::verificar();

$db = Database::conectar();

$servicio_id = isset($_GET['servicio']) ? (int)$_GET['servicio'] : 0;

if ($servicio_id <= 0) die("Servicio inválido");

/* =========================
   SERVICIO
========================= */

$stmt = $db->prepare("SELECT * FROM servicios WHERE id=?");
$stmt->execute([$servicio_id]);
$servicio = $stmt->fetch();

if(!$servicio) die("No existe");

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
$tareas = $stmt->fetchAll();

/* =========================
   PDF
========================= */

$pdf = new TCPDF();
$pdf->AddPage();

$html = "
<h2>Servicio: {$servicio['codigo']}</h2>
<p><b>Cliente:</b> {$servicio['cliente']}</p>
<p><b>Origen:</b> {$servicio['origen']} | <b>Destino:</b> {$servicio['destino']}</p>
<hr>
<h3>Tareas</h3>
<table border='1' cellpadding='4'>
<tr style='background-color:#333;color:#fff;'>
<th>Título</th>
<th>Responsable</th>
<th>Prioridad</th>
<th>Estado</th>
<th>Fecha</th>
</tr>
";

foreach($tareas as $t){

$html .= "
<tr>
<td>{$t['titulo']}</td>
<td>".($t['responsable'] ?? 'N/A')."</td>
<td>{$t['prioridad']}</td>
<td>{$t['estado']}</td>
<td>{$t['fecha_limite']}</td>
</tr>
";
}

$html .= "</table>";

$pdf->writeHTML($html);
$pdf->Output("tareas_servicio.pdf","I");