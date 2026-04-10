<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('tareas.editar'); // 🔥 CAMBIO

$db = Database::conectar();

$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM tareas WHERE id = ?");
$stmt->execute([$id]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    echo "Tarea no encontrada";
    exit;
}

$usuarios = $db->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nuevoResponsable = $_POST['usuario'];
    $responsableAnterior = $tarea['responsable_id'];

    $stmt = $db->prepare("
        UPDATE tareas 
        SET titulo=?, descripcion=?, responsable_id=?, fecha_limite=?
        WHERE id=?
    ");

    $stmt->execute([
        $_POST['titulo'],
        $_POST['descripcion'],
        $nuevoResponsable,
        $_POST['fecha'],
        $id
    ]);

    header("Location: ver_servicio.php?id=" . $tarea['servicio_id']);
    exit();
}
?>