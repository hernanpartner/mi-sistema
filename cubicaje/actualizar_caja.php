<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar(); // 🔐 PROTECCIÓN

header('Content-Type: application/json');

// 🔥 SOLO ADMIN PUEDE MODIFICAR
Auth::solo('ADMIN');

// 🔥 LEER JSON
$data = json_decode(file_get_contents("php://input"), true);

// 🔥 VALIDACIONES SEGURAS
$id = isset($data['id']) ? intval($data['id']) : 0;
$campo = isset($data['campo']) ? trim($data['campo']) : '';
$valor = isset($data['valor']) ? intval($data['valor']) : 0;

// 🔥 VALIDAR ID
if($id <= 0){
    echo json_encode([
        'ok' => false,
        'error' => 'ID inválido'
    ]);
    exit;
}

// 🔥 CAMPOS PERMITIDOS
$permitidos = ['apilable','rotable'];

if(!in_array($campo, $permitidos)){
    echo json_encode([
        'ok' => false,
        'error' => 'Campo no permitido'
    ]);
    exit;
}

// 🔥 NORMALIZAR VALOR (0 o 1)
$valor = $valor ? 1 : 0;

try {

    $db = Database::conectar();

    // 🔥 EVITAR SQL DINÁMICO
    if($campo === 'apilable'){
        $stmt = $db->prepare("UPDATE cubicaje SET apilable=? WHERE id=?");
    } else {
        $stmt = $db->prepare("UPDATE cubicaje SET rotable=? WHERE id=?");
    }

    $stmt->execute([$valor, $id]);

    echo json_encode([
        'ok' => true
    ]);

} catch(Exception $e){

    error_log("Error actualizar_caja: " . $e->getMessage());

    echo json_encode([
        'ok' => false,
        'error' => 'Error interno'
    ]);
}