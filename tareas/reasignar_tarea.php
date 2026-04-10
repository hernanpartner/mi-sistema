<?php
require_once __DIR__ . "/../login/Auth.php";
require_once __DIR__ . "/../config/database.php";

Auth::verificar();

$db = Database::conectar();

$tarea_id = isset($_GET['tarea']) ? (int)$_GET['tarea'] : 0;
$servicio_id = isset($_GET['servicio']) ? (int)$_GET['servicio'] : 0;

if(!$tarea_id || !$servicio_id){
    header("Location: index.php");
    exit;
}

/* =========================
   TAREA
========================= */
$stmt = $db->prepare("
    SELECT t.*, u.nombre as responsable_nombre
    FROM tareas t
    LEFT JOIN usuarios u ON t.responsable_id = u.id
    WHERE t.id = ?
");
$stmt->execute([$tarea_id]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$tarea){
    header("Location: index.php");
    exit;
}

/* =========================
   USUARIOS
========================= */
$usuarios = $db->query("SELECT id, nombre FROM usuarios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   VALIDAR RESPONSABLE
========================= */
if($tarea['responsable_id'] != $_SESSION['usuario_id']){
    die("No autorizado");
}

ob_start();
?>

<div class="card">
<div class="card-header">
    <h3>Reasignar tarea: <?php echo htmlspecialchars($tarea['titulo']); ?></h3>
</div>

<div class="card-body">

<form method="POST" action="guardar_reasignacion.php">

<input type="hidden" name="tarea" value="<?php echo $tarea_id; ?>">

<div class="mb-3">
<label>Nuevo responsable</label>
<select name="usuario" class="form-select" required>
<?php foreach($usuarios as $u){ ?>
<option value="<?php echo $u['id']; ?>"
<?php if($u['id'] == $tarea['responsable_id']) echo "selected"; ?>>
<?php echo htmlspecialchars($u['nombre']); ?>
</option>
<?php } ?>
</select>
</div>

<div class="mb-3">
<label>Descripción</label>
<textarea name="descripcion" class="form-control" required><?php echo htmlspecialchars($tarea['descripcion']); ?></textarea>
</div>

<div class="mb-3">
<label>Fecha límite</label>
<input type="date" name="fecha" class="form-control"
value="<?php echo $tarea['fecha_limite']; ?>" required>
</div>

<button class="btn btn-primary">Guardar cambios</button>
<a href="ver_servicio.php?id=<?php echo $servicio_id; ?>" class="btn btn-secondary">Cancelar</a>

</form>

</div>
</div>

<?php
$contenido = ob_get_clean();
$titulo = "Reasignar tarea";
require_once __DIR__ . "/../layouts/app.php";