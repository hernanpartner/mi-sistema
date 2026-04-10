<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

header('Content-Type: application/json');

$db = Database::conectar();

// 🔥 DETECTAR AJAX
$esAjax = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

// 🔥 PROYECTO ACTIVO
$proyecto_id = $_SESSION['proyecto_id'] ?? 0;

if(!$proyecto_id){

    if($esAjax){
        echo json_encode([
            'ok' => false,
            'error' => 'No hay proyecto activo'
        ]);
        exit;
    }

    die("No hay proyecto activo");
}

// 🔥 VALIDACIÓN SEGURA
$largo     = isset($_POST['largo']) ? floatval($_POST['largo']) : 0;
$ancho     = isset($_POST['ancho']) ? floatval($_POST['ancho']) : 0;
$alto      = isset($_POST['alto']) ? floatval($_POST['alto']) : 0;
$peso_max  = isset($_POST['peso_max']) ? floatval($_POST['peso_max']) : 0;
$apilado   = isset($_POST['apilado']) ? intval($_POST['apilado']) : 0;
$rotacion  = isset($_POST['rotacion']) ? intval($_POST['rotacion']) : 0;
$modo      = isset($_POST['modo']) ? trim($_POST['modo']) : '';

// 🔥 VALIDAR DATOS BÁSICOS
if($largo <= 0 || $ancho <= 0 || $alto <= 0){

    if($esAjax){
        echo json_encode([
            'ok' => false,
            'error' => 'Dimensiones inválidas'
        ]);
        exit;
    }

    die("Dimensiones inválidas");
}

try {

    // 🔥 GUARDAR EN BD
    $stmt = $db->prepare("
        REPLACE INTO configuracion_contenedor
        (proyecto_id,largo,ancho,alto,peso_max,apilado,rotacion,modo)
        VALUES (?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $proyecto_id,
        $largo,
        $ancho,
        $alto,
        $peso_max,
        $apilado,
        $rotacion,
        $modo
    ]);

    // 🔥 GUARDAR EN SESIÓN
    $_SESSION['contenedor'] = [
        'largo'=>$largo,
        'ancho'=>$ancho,
        'alto'=>$alto,
        'peso_max'=>$peso_max,
        'apilado'=>$apilado,
        'rotacion'=>$rotacion,
        'modo'=>$modo
    ];

    // 🔥 RESPUESTA AJAX
    if($esAjax){
        echo json_encode([
            'ok' => true
        ]);
        exit;
    }

    // 🔥 COMPATIBILIDAD (NO ROMPE NADA)
    header("Location: index.php");
    exit;

} catch(Exception $e){

    error_log("Error guardar_config: " . $e->getMessage());

    if($esAjax){
        echo json_encode([
            'ok' => false,
            'error' => 'Error interno'
        ]);
        exit;
    }

    die("Error al guardar configuración");
}