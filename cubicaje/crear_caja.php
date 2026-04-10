<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
Auth::solo('ADMIN');

header('Content-Type: application/json');

$db = Database::conectar();

$proyecto_id = $_POST['proyecto_id'] ?? 0;

$nombre = $_POST['nombre'] ?? '';
$largo = $_POST['largo'] ?? 0;
$ancho = $_POST['ancho'] ?? 0;
$alto = $_POST['alto'] ?? 0;
$peso = $_POST['peso'] ?? 0;
$cantidad = $_POST['cantidad'] ?? 0;
$color = $_POST['color'] ?? '#000000';

if(!$proyecto_id || !$nombre){
    echo json_encode(['ok'=>false,'error'=>'Datos inválidos']);
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

    $id = $db->lastInsertId();

    echo json_encode([
        'ok'=>true,
        'caja'=>[
            'id'=>$id,
            'nombre'=>$nombre,
            'largo'=>$largo,
            'ancho'=>$ancho,
            'alto'=>$alto,
            'peso'=>$peso,
            'cantidad'=>$cantidad,
            'color'=>$color,
            'apilable'=>1,
            'rotable'=>1
        ]
    ]);

}catch(Exception $e){
    echo json_encode(['ok'=>false,'error'=>'Error al crear']);
}