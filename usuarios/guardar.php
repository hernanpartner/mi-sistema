<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
Auth::solo('ADMIN');

header('Content-Type: application/json');

$db = Database::conectar();

/* =========================
   RECIBIR JSON
========================= */
$data = json_decode(file_get_contents("php://input"), true);

$nombre  = trim($data['nombre'] ?? '');
$usuario = trim($data['usuario'] ?? '');
$password = $data['password'] ?? '';
$rol = $data['rol'] ?? 'USER';

/* =========================
   VALIDACIONES
========================= */
if ($nombre === '' || $usuario === '' || $password === '') {
    echo json_encode(['ok'=>false, 'error'=>'Datos incompletos']);
    exit;
}

/* =========================
   DUPLICADOS
========================= */
$stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario = ?");
$stmt->execute([$usuario]);

if ($stmt->fetch()) {
    echo json_encode(['ok'=>false, 'error'=>'Usuario ya existe']);
    exit;
}

/* =========================
   INSERTAR
========================= */
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO usuarios (nombre, usuario, password, rol)
    VALUES (?, ?, ?, ?)
");

$stmt->execute([$nombre, $usuario, $hash, $rol]);

echo json_encode(['ok'=>true]);