<?php
session_start();

header("Content-Type: application/msword");
header("Content-Disposition: attachment; filename=simulacion.doc");

$data = $_SESSION['resultado_cubicaje'] ?? [];

echo "<h2>Simulación de Cubicaje</h2>";

foreach($data as $i=>$cont){

echo "<h3>Contenedor ".($i+1)."</h3>";

foreach($cont['cajas'] as $c){

echo "Caja: {$c['nombre']} - {$c['l']}x{$c['a']}x{$c['h']}<br>";

}

echo "<br>";
}