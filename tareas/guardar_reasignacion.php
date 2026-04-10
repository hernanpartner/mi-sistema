<?php 

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

header('Content-Type: application/json');

$db = Database::conectar();

$tarea_id     = (int)$_POST['tarea'];
$nuevoUsuario = (int)$_POST['usuario'];
$nuevaDescripcion = trim($_POST['descripcion']);
$nuevaFecha   = $_POST['fecha'];

try {

    $db->beginTransaction();

    // 🔍 Obtener tarea actual
    $stmt = $db->prepare("
        SELECT t.*, u.nombre as responsable_nombre
        FROM tareas t
        LEFT JOIN usuarios u ON t.responsable_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$tarea_id]);
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarea) {
        throw new Exception("Tarea no encontrada");
    }

    $servicio_id = (int)$tarea['servicio_id'];

    if ($tarea['responsable_id'] != $_SESSION['usuario_id']) {
        throw new Exception("No permitido");
    }

    // 🔍 Actor
    $stmtActor = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmtActor->execute([$_SESSION['usuario_id']]);
    $nombreActor = $stmtActor->fetchColumn() ?? 'Usuario';

    // 🔍 Nuevo usuario
    $stmtUser = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmtUser->execute([$nuevoUsuario]);
    $nombreNuevo = $stmtUser->fetchColumn() ?? 'Usuario';

    // 🔥 UPDATE
    $stmt = $db->prepare("
        UPDATE tareas 
        SET responsable_id = ?, descripcion = ?, fecha_limite = ?, asignado_por = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $nuevoUsuario,
        $nuevaDescripcion,
        $nuevaFecha,
        $_SESSION['usuario_id'],
        $tarea_id
    ]);

    // =========================
    // 📜 HISTORIAL COMPLETO (FIX REAL)
    // =========================

    $acciones = [];

    // 🔁 REASIGNACIÓN
    if ($tarea['responsable_id'] != $nuevoUsuario) {
        $acciones[] = "$nombreActor reasignó a $nombreNuevo";
    }

    // ✏️ DESCRIPCIÓN
    if ($tarea['descripcion'] != $nuevaDescripcion) {
        $acciones[] = "$nombreActor actualizó la descripción: $nuevaDescripcion";
    }

    // 📅 FECHA
    if ($tarea['fecha_limite'] != $nuevaFecha) {
        $acciones[] = "$nombreActor cambió la fecha límite a $nuevaFecha";
    }

    foreach ($acciones as $accion) {

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
    }

    // =========================
    // 🔔 NOTIFICACIÓN
    // =========================

    $mensaje = $nombreActor . " te asignó la tarea: " . $tarea['titulo'];

    $stmtNotif = $db->prepare("
        INSERT INTO notificaciones (usuario_id, mensaje, leido, fecha, tarea_id, servicio_id)
        VALUES (?, ?, 0, NOW(), ?, ?)
    ");

    $stmtNotif->execute([
        $nuevoUsuario,
        $mensaje,
        $tarea_id,
        $servicio_id
    ]);

    $db->commit();

    echo json_encode([
        "ok" => true
    ]);

} catch (Exception $e) {

    $db->rollBack();

    echo json_encode([
        "ok" => false,
        "error" => $e->getMessage()
    ]);
}