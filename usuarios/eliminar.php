<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('usuarios.eliminar'); // 🔥 CAMBIO

$db = Database::conectar();

$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id=?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: index.php");
    exit;
}

// 🔥 ELIMINAR SI CONFIRMA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $db->prepare("DELETE FROM usuarios WHERE id=?");
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}

// 🔥 CONTENIDO
ob_start();
?>

<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title">Eliminar Usuario</h3>
    </div>

    <div class="card-body text-center">
        <h5>¿Estás seguro de eliminar este usuario?</h5>

        <p>
            <b><?php echo htmlspecialchars($usuario['nombre']); ?></b><br>
            (<?php echo htmlspecialchars($usuario['usuario']); ?>)
        </p>

        <form method="POST">
            <button class="btn btn-danger">
                <i class="bi bi-trash"></i> Sí, eliminar
            </button>

            <a href="index.php" class="btn btn-success">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<?php
$contenido = ob_get_clean();
$titulo = "Eliminar Usuario";

require_once __DIR__ . "/../layouts/app.php";