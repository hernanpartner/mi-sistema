<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
Auth::solo('ADMIN');

header('Content-Type: application/json');

$db = Database::conectar();

$id = $_POST['id'] ?? 0;
$campo = $_POST['campo'] ?? '';
$valor = $_POST['valor'] ?? 0;

if(!$id || !in_array($campo,['apilable','rotable'])){
    echo json_encode(['ok'=>false,'error'=>'Datos inválidos']);
    exit;
}

try{

    $stmt = $db->prepare("UPDATE cubicaje SET $campo=? WHERE id=?");
    $stmt->execute([$valor,$id]);

    echo json_encode(['ok'=>true]);

}catch(Exception $e){
    echo json_encode(['ok'=>false,'error'=>'Error']);
}