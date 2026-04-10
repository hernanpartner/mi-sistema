<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
Auth::solo('ADMIN');

header('Content-Type: application/json');

$db = Database::conectar();

$id = $_POST['id'] ?? 0;
$nombre = $_POST['nombre'] ?? '';

if(!$id || !$nombre){
    echo json_encode([
        'ok' => false,
        'error' => 'Datos inválidos'
    ]);
    exit;
}

try {

    $stmt = $db->prepare("UPDATE proyectos SET nombre=? WHERE id=?");
    $stmt->execute([$nombre, $id]);

    echo json_encode([
        'ok' => true
    ]);

} catch(Exception $e){

    echo json_encode([
        'ok' => false,
        'error' => 'Error al actualizar'
    ]);
}