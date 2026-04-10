<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

header('Content-Type: application/json');

$db = Database::conectar();

// 🔥 VALIDAR PERMISO (MEJOR QUE solo ADMIN)
require_once "../login/Permisos.php";

if(!Permisos::puede('cubicaje.crear')){
    echo json_encode([
        'ok' => false,
        'error' => 'Sin permisos'
    ]);
    exit;
}

// 🔥 DATOS
$nombre = trim($_POST['nombre'] ?? '');

// 🔥 VALIDACIONES
if(strlen($nombre) < 2){
    echo json_encode([
        'ok' => false,
        'error' => 'Nombre demasiado corto'
    ]);
    exit;
}

if(strlen($nombre) > 100){
    echo json_encode([
        'ok' => false,
        'error' => 'Nombre demasiado largo'
    ]);
    exit;
}

try {

    $stmt = $db->prepare("
        INSERT INTO proyectos (nombre, fecha)
        VALUES (?, NOW())
    ");

    $stmt->execute([$nombre]);

    $id = $db->lastInsertId();

    echo json_encode([
        'ok' => true,
        'proyecto' => [
            'id' => $id,
            'nombre' => $nombre,
            'fecha' => date('Y-m-d H:i:s')
        ]
    ]);

} catch(Exception $e){

    error_log("Error crear_proyecto: " . $e->getMessage());

    echo json_encode([
        'ok' => false,
        'error' => 'Error al crear'
    ]);
}