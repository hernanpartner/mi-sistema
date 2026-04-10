<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=servicios.doc");

/* =========================
   DATOS
========================= */

$stmt = $db->query("
    SELECT s.*, c.nombre as categoria
    FROM servicios s
    LEFT JOIN categorias c ON s.categoria_id = c.id
    ORDER BY s.id DESC
");

echo "<h2>Reporte de Servicios</h2>";
echo "<p>Fecha: ".date('d/m/Y H:i')."</p>";

echo "<table border='1' cellpadding='5'>";

echo "<tr>
<th>Código</th>
<th>Cliente</th>
<th>Origen</th>
<th>Destino</th>
<th>ETD</th>
<th>ETA</th>
<th>Estado</th>
<th>Categoría</th>
</tr>";

while($s = $stmt->fetch()){

    $estado = 'Programado';

    if($s['etd'] && $s['eta']){
        $ahora = date('Y-m-d H:i:s');

        if($ahora < $s['etd']) $estado = 'Programado';
        elseif($ahora < $s['eta']) $estado = 'En tránsito';
        else $estado = 'Arribado';
    }

    echo "<tr>
    <td>{$s['codigo']}</td>
    <td>{$s['cliente']}</td>
    <td>{$s['origen']}</td>
    <td>{$s['destino']}</td>
    <td>{$s['etd']}</td>
    <td>{$s['eta']}</td>
    <td>{$estado}</td>
    <td>{$s['categoria']}</td>
    </tr>";
}

echo "</table>";