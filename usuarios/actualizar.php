<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();

// 🔥 PERMISO CORRECTO
Permisos::requerir('usuarios.editar');

header('Content-Type: application/json');

$db = Database::conectar();

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$nombre  = trim($data['nombre'] ?? '');
$usuario = trim($data['usuario'] ?? '');
$password = $data['password'] ?? '';
$rol = $data['rol'] ?? 'USER';

if ($id <= 0 || $nombre === '' || $usuario === '') {
    echo json_encode(['ok'=>false, 'error'=>'Datos inválidos']);
    exit;
}

/* =========================
   UPDATE
========================= */
if ($password !== '') {

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        UPDATE usuarios
        SET nombre=?, usuario=?, password=?, rol=?
        WHERE id=?
    ");

    $stmt->execute([$nombre, $usuario, $hash, $rol, $id]);

} else {

    $stmt = $db->prepare("
        UPDATE usuarios
        SET nombre=?, usuario=?, rol=?
        WHERE id=?
    ");

    $stmt->execute([$nombre, $usuario, $rol, $id]);
}

echo json_encode(['ok'=>true]);