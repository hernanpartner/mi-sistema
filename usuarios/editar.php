<?php
require_once "../login/Auth.php";
require_once "../login/Permisos.php";
require_once "../config/database.php";

Auth::verificar();
Permisos::requerir('usuarios.editar'); // 🔥 CAMBIO

$db = Database::conectar();

$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id=?");
$stmt->execute([$id]);
$u = $stmt->fetch();

if(!$u){
    die("Usuario no existe");
}

$error = "";

if($_POST){

    $stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario=? AND id!=?");
    $stmt->execute([$_POST['usuario'], $id]);

    if($stmt->fetch()){
        $error = "⚠️ Usuario ya existe";
    } else {

        if(!empty($_POST['password'])){
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                UPDATE usuarios 
                SET nombre=?, usuario=?, password=?, rol=? 
                WHERE id=?
            ");

            $stmt->execute([
                $_POST['nombre'],
                $_POST['usuario'],
                $password,
                $_POST['rol'],
                $id
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE usuarios 
                SET nombre=?, usuario=?, rol=? 
                WHERE id=?
            ");

            $stmt->execute([
                $_POST['nombre'],
                $_POST['usuario'],
                $_POST['rol'],
                $id
            ]);
        }

        header("Location: index.php");
        exit;
    }
}

// 🔥 CONTENIDO
ob_start();
?>

<h3>Editar Usuario</h3>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<form method="POST" class="card p-3">

<div class="mb-3">
<label>Nombre</label>
<input name="nombre" value="<?php echo $u['nombre']; ?>" class="form-control">
</div>

<div class="mb-3">
<label>Usuario</label>
<input name="usuario" value="<?php echo $u['usuario']; ?>" class="form-control">
</div>

<div class="mb-3">
<label>Nueva Password</label>
<input type="password" name="password" class="form-control">
</div>

<div class="mb-3">
<label>Rol</label>
<select name="rol" class="form-select">
<option value="ADMIN" <?php if($u['rol']=='ADMIN') echo 'selected'; ?>>ADMIN</option>
<option value="USER" <?php if($u['rol']=='USER') echo 'selected'; ?>>USER</option>
</select>
</div>

<button class="btn btn-success">Guardar</button>
<a href="index.php" class="btn btn-secondary">Volver</a>

</form>

<?php
$contenido = ob_get_clean();
$titulo = "Editar Usuario";

require_once __DIR__ . "/../layouts/app.php";