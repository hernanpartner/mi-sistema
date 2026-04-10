<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

header('Content-Type: application/json');

$db = Database::conectar();

$data = json_decode(file_get_contents("php://input"), true);

$tarea_id = (int)($data['id'] ?? 0);
$nuevo_estado = trim($data['estado'] ?? '');

if ($tarea_id <= 0 || $nuevo_estado == '') {
    echo json_encode(["ok" => false, "error" => "Datos inválidos"]);
    exit;
}

try {

    $db->beginTransaction();

    // 🔍 Obtener tarea actual
    $stmt = $db->prepare("
        SELECT t.*, u.nombre AS responsable_nombre
        FROM tareas t
        LEFT JOIN usuarios u ON t.responsable_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$tarea_id]);
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarea) {
        throw new Exception("Tarea no encontrada");
    }

    $estado_actual = $tarea['estado'];
    $servicio_id = (int)$tarea['servicio_id'];

    if ($estado_actual === $nuevo_estado) {
        echo json_encode(["ok" => true]);
        exit;
    }

    // 🔍 Usuario actor
    $stmtUser = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmtUser->execute([$_SESSION['usuario_id']]);
    $nombreActor = $stmtUser->fetchColumn() ?? 'Usuario';

    // 🔥 UPDATE estado
    $stmt = $db->prepare("
        UPDATE tareas 
        SET estado = ?
        WHERE id = ?
    ");
    $stmt->execute([$nuevo_estado, $tarea_id]);

    // =========================
    // 📜 HISTORIAL
    // =========================
    $accion = "$nombreActor cambió el estado de '$estado_actual' a '$nuevo_estado'";

    $stmtHist = $db->prepare("
        INSERT INTO historial_tareas (tarea_id, servicio_id, usuario_id, accion, fecha)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmtHist->execute([
        $tarea_id,
        $servicio_id,
        $_SESSION['usuario_id'],
        $accion
    ]);

    // =========================
    // 🔔 NOTIFICACIÓN
    // =========================
    if ($tarea['responsable_id'] != $_SESSION['usuario_id']) {

        $mensaje = $nombreActor . " cambió el estado de la tarea: " . $tarea['titulo'] . " a " . $nuevo_estado;

        $stmtNotif = $db->prepare("
            INSERT INTO notificaciones (usuario_id, mensaje, leido, fecha, tarea_id, servicio_id)
            VALUES (?, ?, 0, NOW(), ?, ?)
        ");

        $stmtNotif->execute([
            $tarea['responsable_id'],
            $mensaje,
            $tarea_id,
            $servicio_id
        ]);
    }

    $db->commit();

    echo json_encode([
        "ok" => true,
        "nuevo_estado" => $nuevo_estado
    ]);

} catch (Exception $e) {

    $db->rollBack();

    echo json_encode([
        "ok" => false,
        "error" => $e->getMessage()
    ]);
}