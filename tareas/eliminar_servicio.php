<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir(['ADMIN']);

$db = Database::conectar();

/* =========================
   AJAX
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? 0;

    if ($id <= 0) {
        echo json_encode(['ok'=>false,'error'=>'ID inválido']);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM servicios WHERE id=?");
    $stmt->execute([$id]);

    echo json_encode(['ok'=>true]);
    exit;
}

/* =========================
   FALLBACK (NO ROMPER SISTEMA)
========================= */

$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM servicios WHERE id=?");
$stmt->execute([$id]);
$servicio = $stmt->fetch();

if (!$servicio) {
    header("Location: index.php");
    exit;
}

ob_start();
?>

<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title">Eliminar Servicio</h3>
    </div>

    <div class="card-body text-center">
        <h5>¿Seguro que deseas eliminar este servicio?</h5>

        <p>
            <b><?php echo htmlspecialchars($servicio['codigo']); ?></b>
        </p>

        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <button class="btn btn-danger">
                <i class="bi bi-trash"></i> Sí, eliminar
            </button>

            <a href="index.php" class="btn btn-success">
                Cancelar
            </a>
        </form>
    </div>
</div>

<?php
$contenido = ob_get_clean();
$titulo = "Eliminar Servicio";

require_once __DIR__ . "/../layouts/app.php";