<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

// 🔥 SOLO ADMIN PUEDE ELIMINAR
Auth::solo('ADMIN');

header('Content-Type: application/json');

$db = Database::conectar();

// 🔥 DETECTAR MÉTODO (POST o GET fallback)
$id = $_POST['id'] ?? $_GET['id'] ?? null;

// 🔥 VALIDAR ID
if(!$id || !is_numeric($id)){
    echo json_encode([
        'ok' => false,
        'error' => 'ID inválido'
    ]);
    exit;
}

try {

    $db->beginTransaction();

    // 🔥 eliminar cajas relacionadas primero
    $stmt = $db->prepare("DELETE FROM cubicaje WHERE proyecto_id=?");
    $stmt->execute([$id]);

    // 🔥 eliminar proyecto
    $stmt = $db->prepare("DELETE FROM proyectos WHERE id=?");
    $stmt->execute([$id]);

    $db->commit();

    // 🔥 SI ES AJAX
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
    ) {
        echo json_encode([
            'ok' => true
        ]);
        exit;
    }

    // 🔥 SI ES NORMAL (compatibilidad)
    header("Location: index.php");
    exit;

} catch (Exception $e) {

    $db->rollBack();

    echo json_encode([
        'ok' => false,
        'error' => 'Error al eliminar'
    ]);
}