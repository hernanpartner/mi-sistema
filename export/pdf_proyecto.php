<?php
require_once "../login/Auth.php";
require_once "../config/database.php";
require_once "../libs/tcpdf/tcpdf.php";

Auth::verificar();

$db = Database::conectar();

$proyecto_id = $_GET['id'] ?? 0;

// PROYECTO
$stmt = $db->prepare("SELECT * FROM proyectos WHERE id=?");
$stmt->execute([$proyecto_id]);
$proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$proyecto){
    die("Proyecto no encontrado");
}

// CAJAS
$stmt = $db->prepare("SELECT * FROM cubicaje WHERE proyecto_id=?");
$stmt->execute([$proyecto_id]);
$cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PDF
$pdf = new TCPDF();
$pdf->SetTitle('Proyecto Cubicaje');
$pdf->AddPage();

$html = "<h2>Proyecto: {$proyecto['nombre']}</h2><br>";

$html .= "<table border='1' cellpadding='5'>
<tr>
<th>Nombre</th>
<th>Largo</th>
<th>Ancho</th>
<th>Alto</th>
<th>Peso</th>
<th>Cantidad</th>
</tr>";

foreach($cajas as $c){
    $html .= "<tr>
        <td>{$c['nombre']}</td>
        <td>{$c['largo']}</td>
        <td>{$c['ancho']}</td>
        <td>{$c['alto']}</td>
        <td>{$c['peso']}</td>
        <td>{$c['cantidad']}</td>
    </tr>";
}

$html .= "</table>";

$pdf->writeHTML($html);
$pdf->Output("proyecto_$proyecto_id.pdf","I");