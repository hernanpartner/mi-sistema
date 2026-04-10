<?php
require_once __DIR__ . "/../login/Auth.php";
require_once __DIR__ . "/../login/Permisos.php";
require_once __DIR__ . "/../config/database.php";

Auth::verificar();

// 🔥 PERMISO CORRECTO
Permisos::requerir('usuarios.crear');

$db = Database::conectar();

$error = "";

if($_POST){

    $stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario=?");
    $stmt->execute([$_POST['usuario']]);

    if($stmt->fetch()){
        $error = "⚠️ Usuario ya existe";
    } else {

        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO usuarios (nombre, usuario, password, rol)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['nombre'],
            $_POST['usuario'],
            $password,
            $_POST['rol']
        ]);

        header("Location: index.php");
        exit;
    }
}

ob_start();
?>

<h3>Crear Usuario</h3>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<form method="POST" class="card p-3">

<div class="mb-3">
<label>Nombre</label>
<input name="nombre" class="form-control" required>
</div>

<div class="mb-3">
<label>Usuario</label>
<input name="usuario" class="form-control" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label>Rol</label>
<select name="rol" class="form-select">
<option value="ADMIN">ADMIN</option>
<option value="USER">USER</option>
</select>
</div>

<button class="btn btn-success">Guardar</button>
<a href="index.php" class="btn btn-secondary">Volver</a>

</form>

<?php
$contenido = ob_get_clean();
$titulo = "Crear Usuario";

// 🔥 RUTA CORRECTA
require_once __DIR__ . "/../layouts/app.php";