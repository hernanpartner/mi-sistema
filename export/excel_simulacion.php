<?php
session_start();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=simulacion.xls");

$data = $_SESSION['resultado_cubicaje'] ?? [];

echo "<table border='1'>";

foreach($data as $i=>$cont){

echo "<tr><th colspan='5'>Contenedor ".($i+1)."</th></tr>";
echo "<tr>
<th>Nombre</th><th>Largo</th><th>Ancho</th><th>Alto</th><th>Peso</th>
</tr>";

foreach($cont['cajas'] as $c){

echo "<tr>
<td>{$c['nombre']}</td>
<td>{$c['l']}</td>
<td>{$c['a']}</td>
<td>{$c['h']}</td>
<td>{$c['peso']}</td>
</tr>";

}

}

echo "</table>";