<?php

require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('cubicaje.editar'); // 🔥 CAMBIO

header('Content-Type: application/json');

// 🔥 LEER JSON
$data = json_decode(file_get_contents("php://input"), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$campo = isset($data['campo']) ? trim($data['campo']) : '';
$valor = isset($data['valor']) ? intval($data['valor']) : 0;

if($id <= 0){
    echo json_encode(['ok'=>false,'error'=>'ID inválido']);
    exit;
}

$permitidos = ['apilable','rotable'];

if(!in_array($campo, $permitidos)){
    echo json_encode(['ok'=>false,'error'=>'Campo no permitido']);
    exit;
}

$valor = $valor ? 1 : 0;

try {

    $db = Database::conectar();

    if($campo === 'apilable'){
        $stmt = $db->prepare("UPDATE cubicaje SET apilable=? WHERE id=?");
    } else {
        $stmt = $db->prepare("UPDATE cubicaje SET rotable=? WHERE id=?");
    }

    $stmt->execute([$valor, $id]);

    echo json_encode(['ok'=>true]);

} catch(Exception $e){

    error_log("Error actualizar_caja: " . $e->getMessage());

    echo json_encode(['ok'=>false,'error'=>'Error interno']);
}