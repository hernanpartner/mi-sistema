<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$usuario_id = $_SESSION['usuario_id'];

// 🔥 SOLO MIS TAREAS (IMPORTANTÍSIMO)
$sql = "
SELECT
COALESCE(SUM(estado = 'PENDIENTE'),0) AS pendientes,
COALESCE(SUM(estado = 'EN PROCESO'),0) AS proceso,
COALESCE(SUM(estado = 'BLOQUEADO'),0) AS bloqueadas,
COALESCE(SUM(estado = 'TERMINADO'),0) AS terminadas,
COALESCE(SUM(fecha_limite < CURDATE() AND estado != 'TERMINADO'),0) AS atrasadas
FROM tareas
WHERE responsable_id = ?
";

$stmt = $db->prepare($sql);
$stmt->execute([$usuario_id]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);