<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

header('Content-Type: application/json');

$db = Database::conectar();

// 🔥 SOLO ADMIN
Auth::solo('ADMIN');

// 🔥 INICIAR SESIÓN (ya la maneja Auth pero aseguramos)
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// 🔐 GENERAR TOKEN SI NO EXISTE
if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 🔐 VALIDAR CSRF (solo si viene)
$csrf = $_POST['csrf_token'] ?? '';

if(!empty($csrf) && $csrf !== $_SESSION['csrf_token']){
    echo json_encode([
        'ok' => false,
        'error' => 'Token inválido'
    ]);
    exit;
}

// 🔥 DATOS
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

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

    $stmt = $db->prepare("INSERT INTO proyectos (nombre,fecha) VALUES (?,NOW())");
    $stmt->execute([$nombre]);

    $id = $db->lastInsertId();

    echo json_encode([
        'ok' => true,
        'proyecto' => [
            'id' => $id,
            'nombre' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'fecha' => date('Y-m-d H:i:s')
        ],
        // 🔐 DEVOLVER TOKEN (IMPORTANTE PARA FRONT)
        'csrf_token' => $_SESSION['csrf_token']
    ]);

} catch(Exception $e){

    error_log("Error crear_proyecto: " . $e->getMessage());

    echo json_encode([
        'ok' => false,
        'error' => 'Error al crear'
    ]);
}