<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('cubicaje.crear');

header('Content-Type: application/json');

$db = Database::conectar();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode([
        'ok' => false,
        'error' => 'Método no permitido'
    ]);
    exit;
}

// 🔥 VALIDACIÓN BÁSICA
$proyecto_id = (int)($_POST['proyecto_id'] ?? 0);

$nombre = trim($_POST['nombre'] ?? '');
$largo = (float)($_POST['largo'] ?? 0);
$ancho = (float)($_POST['ancho'] ?? 0);
$alto = (float)($_POST['alto'] ?? 0);
$peso = (float)($_POST['peso'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 0);
$color = $_POST['color'] ?? '#000000';

if(!$proyecto_id || strlen($nombre) < 1){
    echo json_encode([
        'ok' => false,
        'error' => 'Datos inválidos'
    ]);
    exit;
}

try{

    $stmt = $db->prepare("
        INSERT INTO cubicaje 
        (proyecto_id,nombre,largo,ancho,alto,peso,cantidad,color,apilable,rotable)
        VALUES (?,?,?,?,?,?,?,?,1,1)
    ");

    $stmt->execute([
        $proyecto_id,
        $nombre,
        $largo,
        $ancho,
        $alto,
        $peso,
        $cantidad,
        $color
    ]);

    echo json_encode([
        'ok' => true,
        'id' => $db->lastInsertId()
    ]);

}catch(Exception $e){

    error_log("Error agregar_caja: ".$e->getMessage());

    echo json_encode([
        'ok' => false,
        'error' => 'Error al guardar caja'
    ]);
}