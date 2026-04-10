<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('cubicaje.eliminar'); // 🔥 CAMBIO

header('Content-Type: application/json');

$db = Database::conectar();

$id = $_POST['id'] ?? $_GET['id'] ?? null;

if(!$id || !is_numeric($id)){
    echo json_encode([
        'ok' => false,
        'error' => 'ID inválido'
    ]);
    exit;
}

try {

    $db->beginTransaction();

    $stmt = $db->prepare("DELETE FROM cubicaje WHERE proyecto_id=?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("DELETE FROM proyectos WHERE id=?");
    $stmt->execute([$id]);

    $db->commit();

    echo json_encode(['ok'=>true]);

} catch (Exception $e) {

    $db->rollBack();

    echo json_encode([
        'ok' => false,
        'error' => 'Error al eliminar'
    ]);
}