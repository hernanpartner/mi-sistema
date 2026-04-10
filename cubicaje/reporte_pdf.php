<?php
session_start();

$tcpdfPath = $_SERVER['DOCUMENT_ROOT'] . '/sistema/libs/tcpdf/tcpdf.php';
if(!file_exists($tcpdfPath)){
    die("ERROR: TCPDF no encontrado");
}
require_once($tcpdfPath);

$contenedores = $_SESSION['resultado_cubicaje'] ?? [];

$pdf = new TCPDF();
$pdf->SetCreator('Sistema Cubicaje');
$pdf->SetAuthor('Sistema Logistico');
$pdf->SetTitle('Reporte Profesional');

$pdf->SetMargins(10,10,10);
$pdf->AddPage();

$pdf->SetFont('helvetica','B',16);
$pdf->Cell(0,10,'REPORTE DE CUBICAJE',0,1,'C');

$pdf->Ln(5);

foreach($contenedores as $i => $cont){

    $cajas = $cont['cajas'] ?? [];
    $cantidad = count($cajas);
    $volumen = 0;

    foreach($cajas as $c){
        $volumen += ($c['l'] ?? 0)*($c['a'] ?? 0)*($c['h'] ?? 0);
    }

    /* ===== TITULO ===== */
    $pdf->SetFont('helvetica','B',12);
    $pdf->Cell(0,8,"Contenedor ".($i+1),0,1);

    $pdf->SetFont('helvetica','',10);
    $pdf->Cell(0,6,"Peso: ".($cont['peso'] ?? 0)." kg",0,1);
    $pdf->Cell(0,6,"Cajas: {$cantidad}",0,1);
    $pdf->Cell(0,6,"Volumen: ".number_format($volumen)." cm³",0,1);

    $pdf->Ln(4);

    /* ===== IMÁGENES GRANDES (FIX REAL) ===== */
    $img3d = $_SERVER['DOCUMENT_ROOT']."/temp/cubicaje_{$i}_3d.jpg";
    $img2d = $_SERVER['DOCUMENT_ROOT']."/temp/cubicaje_{$i}_2d.jpg";

    $y = $pdf->GetY();

    if(file_exists($img3d)){
        $pdf->Image($img3d, 10, $y, 90, 70); // GRANDE
    }

    if(file_exists($img2d)){
        $pdf->Image($img2d, 110, $y, 90, 70); // GRANDE
    }

    $pdf->SetY($y + 75);

    /* ===== TABLA ===== */
    $html = '<table border="1" cellpadding="5">
    <tr style="background-color:#eeeeee;">
        <th><b>Nombre</b></th>
        <th><b>Dimensiones</b></th>
        <th><b>Peso</b></th>
        <th><b>Nivel</b></th>
        <th><b>Color</b></th>
    </tr>';

    foreach($cajas as $c){

        $nombre = htmlspecialchars($c['nombre'] ?? 'Caja');
        $l = $c['l'] ?? 0;
        $a = $c['a'] ?? 0;
        $h = $c['h'] ?? 0;
        $peso = $c['peso'] ?? 0;
        $z = $c['z'] ?? 0;

        $nivel = ($z > 0) ? 2 : 1;

        /* ===== COLOR FIX ===== */
        $color = $c['color'] ?? '#000000';

        if(is_numeric($color)){
            $color = sprintf("#%06X", $color);
        }

        list($r,$g,$b) = sscanf($color, "#%02x%02x%02x");

        $html .= "<tr>
            <td>{$nombre}</td>
            <td>{$l} x {$a} x {$h}</td>
            <td>{$peso}</td>
            <td>{$nivel}</td>
            <td style=\"background-color:rgb($r,$g,$b);\"></td>
        </tr>";
    }

    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Ln(10);

    if($i < count($contenedores)-1){
        $pdf->AddPage();
    }
}

$pdf->Output('reporte.pdf','I');