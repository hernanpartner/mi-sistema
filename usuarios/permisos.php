<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

/* 🔥 SIEMPRE INICIAR SESIÓN */
Auth::verificar();

/* 🔥 SOLO ADMIN PUEDE CONFIGURAR */
if (Auth::rol() !== 'ADMIN') {
    die("⛔ Solo ADMIN puede gestionar permisos");
}

$db = Database::conectar();

/* 🔥 SOLO USER (ADMIN NO SE CONFIGURA) */
$roles = ['USER'];

$listaPermisos = Permisos::lista();

/* =========================
   GUARDAR
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rol = strtoupper(trim($_POST['rol']));
    $permisos = $_POST['permisos'] ?? [];

    // 🔥 LIMPIAR PERMISOS DEL ROL
    $stmt = $db->prepare("DELETE FROM permisos WHERE UPPER(TRIM(rol)) = ?");
    $stmt->execute([$rol]);

    // 🔥 INSERTAR NUEVOS
    foreach ($permisos as $p) {

        if (!in_array($p, $listaPermisos)) continue;

        $stmt = $db->prepare("
            INSERT INTO permisos (rol, permiso) 
            VALUES (?,?)
        ");
        $stmt->execute([$rol, trim($p)]);
    }

    header("Location: permisos.php?ok=1");
    exit;
}

/* =========================
   CARGAR ACTUALES
========================= */
$actuales = [];

foreach ($roles as $r) {

    $stmt = $db->prepare("
        SELECT permiso 
        FROM permisos 
        WHERE UPPER(TRIM(rol)) = ?
    ");

    $stmt->execute([$r]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $actuales[$r] = array_map(function($x){
        return trim($x['permiso']);
    }, $data);
}

ob_start();
?>

<h3>Permisos del Sistema</h3>

<?php if(isset($_GET['ok'])){ ?>
<div class="alert alert-success">Guardado correctamente</div>
<?php } ?>

<?php foreach($roles as $rol){ ?>

<form method="POST" class="card mb-3">

<div class="card-header bg-dark text-white">
Rol: <?php echo $rol; ?>
</div>

<div class="card-body">

<input type="hidden" name="rol" value="<?php echo $rol; ?>">

<div class="row">

<?php foreach($listaPermisos as $p){ ?>

<div class="col-md-3 mb-2">

<label class="form-check-label">

<input 
type="checkbox" 
class="form-check-input"
name="permisos[]" 
value="<?php echo $p; ?>"

<?php echo in_array($p, $actuales[$rol]) ? 'checked' : ''; ?>
>

<?php echo $p; ?>

</label>

</div>

<?php } ?>

</div>

</div>

<div class="card-footer text-end">
<button class="btn btn-success">Guardar</button>
</div>

</form>

<?php } ?>

<a href="/sistema/dashboard/" class="btn btn-secondary">Volver</a>

<?php
$contenido = ob_get_clean();
$titulo = "Permisos";
require_once "../layouts/app.php";
?>