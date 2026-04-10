<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('cubicaje.eliminar'); // 🔥 CAMBIO

header('Content-Type: application/json');

$db = Database::conectar();

$id = $_POST['id'] ?? $_GET['id'] ?? 0;

if(!$id){
    echo json_encode(['ok'=>false,'error'=>'ID inválido']);
    exit;
}

try{

    $stmt = $db->prepare("DELETE FROM cubicaje WHERE id=?");
    $stmt->execute([$id]);

    echo json_encode(['ok'=>true]);

}catch(Exception $e){
    echo json_encode(['ok'=>false,'error'=>'Error al eliminar']);
}