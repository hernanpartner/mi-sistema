<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$usuario_id = $_SESSION['usuario_id'];

// 🔥 MISMAS NOTIFICACIONES QUE USAS EN TAREAS
$stmt = $db->prepare("
    SELECT n.*, s.codigo AS servicio_codigo
    FROM notificaciones n
    LEFT JOIN servicios s ON n.servicio_id = s.id
    WHERE n.usuario_id = ? 
    ORDER BY n.fecha DESC 
    LIMIT 30
");
$stmt->execute([$usuario_id]);

$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔥 FORMATO PARA DASHBOARD
$data = [];

foreach($notificaciones as $n){

    $link = "/sistema/tareas/ver_servicio.php?id=" . $n['servicio_id'];

    if (!empty($n['tarea_id'])) {
        $link .= "&tarea=" . $n['tarea_id'];
    }

    $data[] = [
        "id" => $n['id'],
        "mensaje" => (!empty($n['servicio_codigo']) ? "[".$n['servicio_codigo']."] " : "") . $n['mensaje'],
        "link" => $link,
        "leido" => $n['leido']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);