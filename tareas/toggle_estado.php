<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

header('Content-Type: application/json');

$db = Database::conectar();

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? 0;
$campo = $data['campo'] ?? '';
$valor = $data['valor'] ?? '';

$permitidos = ['activo','prioridad']; // ajustable

if(!$id || !in_array($campo,$permitidos)){
    echo json_encode(['ok'=>false,'error'=>'Datos inválidos']);
    exit;
}

try{

    $stmt = $db->prepare("UPDATE servicios SET $campo=? WHERE id=?");
    $stmt->execute([$valor,$id]);

    echo json_encode(['ok'=>true]);

}catch(Exception $e){

    echo json_encode(['ok'=>false,'error'=>'Error al actualizar']);
}