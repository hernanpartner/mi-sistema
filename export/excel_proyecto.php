<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

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

// HEADERS EXCEL
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=proyecto_$proyecto_id.xls");

// CONTENIDO
echo "Proyecto:\t".$proyecto['nombre']."\n\n";

echo "Nombre\tLargo\tAncho\tAlto\tPeso\tCantidad\n";

foreach($cajas as $c){
    echo "{$c['nombre']}\t{$c['largo']}\t{$c['ancho']}\t{$c['alto']}\t{$c['peso']}\t{$c['cantidad']}\n";
}