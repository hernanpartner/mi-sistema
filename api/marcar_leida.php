<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../login/Auth.php";

if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["ok"=>false]);
    exit;
}

header('Content-Type: application/json');

$db = Database::conectar();

$id = (int)($_POST['id'] ?? 0);

$stmt = $db->prepare("
    UPDATE notificaciones
    SET leido = 1
    WHERE id = ? AND usuario_id = ?
");

$stmt->execute([$id, $_SESSION['usuario_id']]);

echo json_encode(["ok"=>true]);