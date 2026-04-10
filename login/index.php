<?php
require_once "../config/database.php";
require_once "Auth.php";

session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: /sistema/dashboard/");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $db = Database::conectar();

    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $recordar = isset($_POST['recordar']);

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        // 🔒 BLOQUEO POR INTENTOS
        if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
            $error = "Cuenta bloqueada temporalmente";
        }

        elseif (password_verify($password, $user['password'])) {

            // RESET INTENTOS
            $stmt = $db->prepare("UPDATE usuarios SET intentos=0, bloqueado_hasta=NULL WHERE id=?");
            $stmt->execute([$user['id']]);

            Auth::login($user, $recordar);

            header("Location: /sistema/dashboard/");
            exit;

        } else {

            // SUMAR INTENTOS
            $intentos = $user['intentos'] + 1;

            if ($intentos >= 5) {

                $bloqueado = date("Y-m-d H:i:s", time() + 300); // 5 min

                $stmt = $db->prepare("UPDATE usuarios SET intentos=?, bloqueado_hasta=? WHERE id=?");
                $stmt->execute([$intentos, $bloqueado, $user['id']]);

                $error = "Cuenta bloqueada por 5 minutos";

            } else {

                $stmt = $db->prepare("UPDATE usuarios SET intentos=? WHERE id=?");
                $stmt->execute([$intentos, $user['id']]);

                $error = "Credenciales incorrectas ($intentos/5)";
            }
        }

    } else {
        $error = "Usuario no encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>

<link rel="stylesheet" href="/sistema/libs/bootstrap/css/bootstrap.min.css">

</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

<div class="card p-4 shadow" style="width:350px;">

<h4 class="text-center mb-3">Sistema Logístico</h4>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<?php if(isset($_GET['expirado'])){ ?>
<div class="alert alert-warning">Sesión expirada</div>
<?php } ?>

<form method="POST">

<input type="text" name="usuario" class="form-control mb-2" placeholder="Usuario" required>

<input type="password" name="password" class="form-control mb-2" placeholder="Contraseña" required>

<div class="form-check mb-3">
<input type="checkbox" name="recordar" class="form-check-input">
<label class="form-check-label">Recordarme</label>
</div>

<button class="btn btn-dark w-100">Ingresar</button>

</form>

</div>

</body>
</html>