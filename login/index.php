<?php
require_once "../config/database.php";
require_once "Auth.php";

session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: /sistema/dashboard/");
    exit;
}

// 🔥 RECUPERAR USUARIO RECORDADO
$usuario_recordado = $_COOKIE['usuario_recordado'] ?? '';

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

        if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
            $error = "Cuenta bloqueada temporalmente";
        }

        elseif (password_verify($password, $user['password'])) {

            // 🔥 RESET INTENTOS
            $stmt = $db->prepare("UPDATE usuarios SET intentos=0, bloqueado_hasta=NULL WHERE id=?");
            $stmt->execute([$user['id']]);

            // 🔥 LOGIN
            Auth::login($user, $recordar);

            // 🔥 RECORDAR USUARIO (ESTO FALTABA)
            if ($recordar) {
                setcookie("usuario_recordado", $usuario, time() + (86400 * 30), "/");
            } else {
                setcookie("usuario_recordado", "", time() - 3600, "/");
            }

            header("Location: /sistema/dashboard/");
            exit;

        } else {

            $intentos = $user['intentos'] + 1;

            if ($intentos >= 5) {

                $bloqueado = date("Y-m-d H:i:s", time() + 300);

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

<!-- 🔥 LOGO -->
<div class="text-center mb-3">
    <img src="/sistema/assets/logo.png" alt="Logo" style="max-width:120px;">
</div>

<h4 class="text-center mb-3">Sistema Logístico</h4>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<?php if(isset($_GET['expirado'])){ ?>
<div class="alert alert-warning">Sesión expirada</div>
<?php } ?>

<form method="POST">

<input 
    type="text" 
    name="usuario" 
    class="form-control mb-2" 
    placeholder="Usuario" 
    required
    value="<?php echo htmlspecialchars($usuario_recordado); ?>"
>

<input 
    type="password" 
    name="password" 
    class="form-control mb-2" 
    placeholder="Contraseña" 
    required
>

<div class="form-check mb-3">
<input type="checkbox" name="recordar" class="form-check-input"
<?php if($usuario_recordado) echo 'checked'; ?>>
<label class="form-check-label">Recordarme</label>
</div>

<button class="btn btn-dark w-100">Ingresar</button>

</form>

</div>

</body>
</html>