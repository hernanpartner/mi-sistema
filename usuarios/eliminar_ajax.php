<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('usuarios.eliminar'); // 🔥 CAMBIO

header('Content-Type: application/json');

$db = Database::conectar();

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok'=>false, 'error'=>'ID inválido']);
    exit;
}

/* =========================
   ELIMINAR
========================= */
$stmt = $db->prepare("DELETE FROM usuarios WHERE id=?");
$stmt->execute([$id]);

echo json_encode(['ok'=>true]);