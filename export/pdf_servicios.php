<?php

require_once "../login/Auth.php";
require_once "../config/database.php";
require_once "../libs/tcpdf/tcpdf.php";

Auth::verificar();

$db = Database::conectar();

date_default_timezone_set('America/La_Paz');

/* =========================
   DATOS
========================= */

$stmt = $db->query("
    SELECT s.*, c.nombre as categoria
    FROM servicios s
    LEFT JOIN categorias c ON s.categoria_id = c.id
    ORDER BY s.id DESC
");

$servicios = $stmt->fetchAll();

/* =========================
   PDF
========================= */

$pdf = new TCPDF();
$pdf->SetCreator('Sistema Logístico');
$pdf->SetAuthor('Sistema');
$pdf->SetTitle('Reporte de Servicios');

$pdf->AddPage();

/* =========================
   TITULO
========================= */

$html = "
<h2 style='text-align:center;'>Reporte de Servicios</h2>
<p>Fecha: ".date('d/m/Y H:i')."</p>
";

/* =========================
   TABLA
========================= */

$html .= "
<table border='1' cellpadding='5'>
<thead>
<tr style='background-color:#343a40;color:white;'>
<th><b>Código</b></th>
<th><b>Cliente</b></th>
<th><b>Origen</b></th>
<th><b>Destino</b></th>
<th><b>ETD</b></th>
<th><b>ETA</b></th>
<th><b>Estado</b></th>
<th><b>Categoría</b></th>
</tr>
</thead>
<tbody>
";

foreach($servicios as $s){

    $estado = 'Programado';

    if($s['etd'] && $s['eta']){
        $ahora = date('Y-m-d H:i:s');

        if($ahora < $s['etd']) $estado = 'Programado';
        elseif($ahora < $s['eta']) $estado = 'En tránsito';
        else $estado = 'Arribado';
    }

    $html .= "
    <tr>
    <td>{$s['codigo']}</td>
    <td>{$s['cliente']}</td>
    <td>{$s['origen']}</td>
    <td>{$s['destino']}</td>
    <td>".($s['etd'] ? date('d/m H:i',strtotime($s['etd'])):'')."</td>
    <td>".($s['eta'] ? date('d/m H:i',strtotime($s['eta'])):'')."</td>
    <td>{$estado}</td>
    <td>{$s['categoria']}</td>
    </tr>
    ";
}

$html .= "</tbody></table>";

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('reporte_servicios.pdf', 'I');