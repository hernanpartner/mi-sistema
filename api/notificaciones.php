<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../login/Auth.php";

// 🔥 NO usar redirecciones en API
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$db = Database::conectar();

$stmt = $db->prepare("
    SELECT id, mensaje, leido, tarea_id, servicio_id
    FROM notificaciones
    WHERE usuario_id = ?
    ORDER BY fecha DESC
    LIMIT 20
");

$stmt->execute([$_SESSION['usuario_id']]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔥 generar link correcto
foreach ($data as &$n) {
    $n['link'] = "/sistema/tareas/ver_servicio.php?id={$n['servicio_id']}&tarea={$n['tarea_id']}&noti={$n['id']}";
}

echo json_encode($data);