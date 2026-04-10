<?php

require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

/* =========================
   FORZAR JSON SIEMPRE
========================= */
header('Content-Type: application/json');

/* =========================
   ASEGURAR SESIÓN
========================= */
Auth::verificar();

/* =========================
   VALIDAR PERMISO
========================= */
if (!Permisos::puede('tareas.cambiar_estado')) {
    echo json_encode(['ok'=>false,'error'=>'Sin permiso']);
    exit;
}

/* =========================
   DB
========================= */
$db = Database::conectar();

/* =========================
   RECIBIR JSON
========================= */
$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$estado = $data['estado'] ?? '';

$estadosValidos = ['PENDIENTE','EN PROCESO','BLOQUEADO','TERMINADO'];

if ($id <= 0 || !in_array($estado, $estadosValidos)) {
    echo json_encode(['ok'=>false,'error'=>'Datos inválidos']);
    exit;
}

/* =========================
   OBTENER TAREA
========================= */
$stmt = $db->prepare("
    SELECT estado, responsable_id, asignado_por, titulo, servicio_id
    FROM tareas 
    WHERE id = ?
");
$stmt->execute([$id]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    echo json_encode(['ok'=>false,'error'=>'No existe']);
    exit;
}

/* =========================
   VALIDAR RESPONSABLE
========================= */
if ($tarea['responsable_id'] != $_SESSION['usuario_id']) {
    echo json_encode(['ok'=>false,'error'=>'No autorizado']);
    exit;
}

/* =========================
   VALIDAR TERMINADO
========================= */
if ($tarea['estado'] === 'TERMINADO') {
    echo json_encode(['ok'=>false,'error'=>'Ya finalizado']);
    exit;
}

/* =========================
   USUARIO
========================= */
$stmtUser = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmtUser->execute([$_SESSION['usuario_id']]);
$usuarioData = $stmtUser->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuarioData['nombre'] ?? 'Usuario';

/* =========================
   UPDATE
========================= */
$stmt = $db->prepare("
    UPDATE tareas 
    SET estado = ?, 
        fecha_finalizado = IF(? = 'TERMINADO', NOW(), fecha_finalizado)
    WHERE id = ?
");
$stmt->execute([$estado, $estado, $id]);

/* =========================
   HISTORIAL
========================= */
try {
    $stmtHist = $db->prepare("
        INSERT INTO historial_tareas (tarea_id, servicio_id, accion, usuario_id, fecha)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmtHist->execute([
        $id,
        $tarea['servicio_id'],
        "$nombreUsuario cambió el estado a $estado",
        $_SESSION['usuario_id']
    ]);
} catch (Exception $e) {}

/* =========================
   NOTIFICACION
========================= */
if ($tarea['asignado_por'] != $_SESSION['usuario_id']) {

    try {
        $stmtNotif = $db->prepare("
            INSERT INTO notificaciones (usuario_id, mensaje, leido, fecha, tarea_id, servicio_id)
            VALUES (?, ?, 0, NOW(), ?, ?)
        ");

        $mensaje = $nombreUsuario . " cambió la tarea '" . $tarea['titulo'] . "' a $estado";

        $stmtNotif->execute([
            $tarea['asignado_por'],
            $mensaje,
            $id,
            $tarea['servicio_id']
        ]);

    } catch (Exception $e) {}
}

echo json_encode(['ok'=>true]);