<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$servicio_id = isset($_GET['servicio']) ? (int)$_GET['servicio'] : 0;

if ($servicio_id <= 0) {
    header("Location: index.php");
    exit;
}

// Servicio
$stmt = $db->prepare("SELECT * FROM servicios WHERE id = ?");
$stmt->execute([$servicio_id]);
$servicio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$servicio) {
    header("Location: index.php");
    exit;
}

// Usuarios
$usuarios = $db->query("SELECT id, nombre FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $usuario = (int)($_POST['usuario'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $prioridad = $_POST['prioridad'] ?? '';

    if (!$titulo || !$descripcion || !$usuario || !$fecha || !$prioridad) {
        $error = "Faltan datos obligatorios";
    }

    if (!$error) {

        try {

            // VALIDAR DUPLICADO
            $stmtDup = $db->prepare("
                SELECT COUNT(*) 
                FROM tareas 
                WHERE servicio_id = ? AND titulo = ?
            ");
            $stmtDup->execute([$servicio_id, $titulo]);

            if ($stmtDup->fetchColumn() > 0) {
                $error = "Ya existe una tarea con ese título en este servicio";
            }

            if (!$error) {

                $db->beginTransaction();

                // Usuario que crea
                $stmtActor = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
                $stmtActor->execute([$_SESSION['usuario_id']]);
                $actorData = $stmtActor->fetch(PDO::FETCH_ASSOC);
                $nombreActor = $actorData['nombre'] ?? 'Usuario';

                // Crear tarea
                $stmt = $db->prepare("
                    INSERT INTO tareas
                    (titulo, descripcion, responsable_id, fecha_limite, servicio_id, prioridad, asignado_por, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')
                ");

                $stmt->execute([
                    $titulo,
                    $descripcion,
                    $usuario,
                    $fecha,
                    $servicio_id,
                    $prioridad,
                    $_SESSION['usuario_id']
                ]);

                $tarea_id = $db->lastInsertId();

                // Usuario asignado
                $stmtUser = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
                $stmtUser->execute([$usuario]);
                $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
                $nombreAsignado = $userData['nombre'] ?? 'Usuario';

                // HISTORIAL
                try {
                    $stmtHist = $db->prepare("
                        INSERT INTO historial_tareas (tarea_id, servicio_id, accion, usuario_id, fecha)
                        VALUES (?, ?, ?, ?, NOW())
                    ");

                    $accion = "$nombreActor creó la tarea: $descripcion y la asignó a $nombreAsignado";

                    $stmtHist->execute([
                        $tarea_id,
                        $servicio_id,
                        $accion,
                        $_SESSION['usuario_id']
                    ]);
                } catch (Exception $e) {}

                // NOTIFICACIÓN
                $mensaje = $nombreActor . " te asignó una nueva tarea: " . $titulo;

                $stmtNotif = $db->prepare("
                    INSERT INTO notificaciones (usuario_id, mensaje, leido, fecha, tarea_id, servicio_id)
                    VALUES (?, ?, 0, NOW(), ?, ?)
                ");

                $stmtNotif->execute([
                    $usuario,
                    $mensaje,
                    $tarea_id,
                    $servicio_id
                ]);

                $db->commit();

                header("Location: ver_servicio.php?id=" . $servicio_id);
                exit;
            }

        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error al crear la tarea";
        }
    }
}

// 🔥 CONTENIDO
ob_start();
?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            Crear Tarea - Servicio: <?php echo htmlspecialchars($servicio['codigo']); ?>
        </h3>
    </div>

    <div class="card-body">

        <?php if($error){ ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">

        <div class="row">

            <div class="col-md-6">
                <label>Título</label>
                <input name="titulo" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Responsable</label>
                <select name="usuario" class="form-select">
                    <?php foreach($usuarios as $u){ ?>
                    <option value="<?php echo $u['id']; ?>">
                        <?php echo htmlspecialchars($u['nombre']); ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

        </div>

        <br>

        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" required></textarea>

        <br>

        <div class="row">

            <div class="col-md-4">
                <label>Prioridad</label>
                <select name="prioridad" class="form-select">
                    <option value="BAJA">BAJA</option>
                    <option value="MEDIA">MEDIA</option>
                    <option value="ALTA">ALTA</option>
                    <option value="URGENTE">URGENTE</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Fecha límite</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>

        </div>

        <br>

        <button class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Crear tarea
        </button>

        <a href="ver_servicio.php?id=<?php echo $servicio_id; ?>" 
           class="btn btn-secondary">
           Cancelar
        </a>

        </form>

    </div>
</div>

<?php
$contenido = ob_get_clean();
$titulo = "Crear Tarea";

require_once __DIR__ . "/../layouts/app.php";