<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
Auth::solo('ADMIN');

$db = Database::conectar();

$id = $_GET['id'] ?? 0;

// Obtener tarea
$stmt = $db->prepare("SELECT * FROM tareas WHERE id = ?");
$stmt->execute([$id]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    echo "Tarea no encontrada";
    exit;
}

// Obtener usuarios
$usuarios = $db->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nuevoResponsable = $_POST['usuario'];
    $responsableAnterior = $tarea['responsable_id'];

    // Actualizar tarea
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

    // =====================
    // HISTORIAL: edición
    // =====================
    $stmtHist = $db->prepare("
        INSERT INTO historial (usuario_id, accion, descripcion, tarea_id, servicio_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmtHist->execute([
        $_SESSION['usuario_id'],
        'Editó tarea',
        'Se modificaron datos de la tarea: ' . $_POST['titulo'],
        $id,
        $tarea['servicio_id']
    ]);

    // =====================
    // SI CAMBIÓ RESPONSABLE
    // =====================
    if ($nuevoResponsable != $responsableAnterior) {

        $stmtUser = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
        $stmtUser->execute([$nuevoResponsable]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // historial adicional
        $stmtHist = $db->prepare("
            INSERT INTO historial (usuario_id, accion, descripcion, tarea_id, servicio_id)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmtHist->execute([
            $_SESSION['usuario_id'],
            'Reasignó tarea',
            'La tarea fue reasignada a ' . $user['nombre'],
            $id,
            $tarea['servicio_id']
        ]);

        // notificación
        $stmtNotif = $db->prepare("
            INSERT INTO notificaciones (usuario_id, mensaje)
            VALUES (?, ?)
        ");

        $stmtNotif->execute([
            $nuevoResponsable,
            'Se te reasignó la tarea: ' . $_POST['titulo']
        ]);
    }

    header("Location: ver_servicio.php?id=" . $tarea['servicio_id']);
    exit();
}
?>

<h2>Editar Tarea</h2>

<form method="POST">

Título:<br>
<input name="titulo" value="<?php echo htmlspecialchars($tarea['titulo']); ?>"><br><br>

Descripción:<br>
<textarea name="descripcion"><?php echo htmlspecialchars($tarea['descripcion']); ?></textarea><br><br>

Responsable:<br>
<select name="usuario">
<?php foreach($usuarios as $u){ ?>
<option value="<?php echo $u['id']; ?>" <?php if($u['id']==$tarea['responsable_id']) echo 'selected'; ?>>
<?php echo htmlspecialchars($u['nombre']); ?>
</option>
<?php } ?>
</select><br><br>

Fecha:<br>
<input type="date" name="fecha" value="<?php echo htmlspecialchars($tarea['fecha_limite']); ?>"><br><br>

<button>Guardar</button>

</form>