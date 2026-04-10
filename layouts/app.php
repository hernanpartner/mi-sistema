<?php  
require_once __DIR__ . "/../login/Auth.php";
Auth::verificar();

$rol = Auth::rol();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?php echo $titulo ?? 'Sistema'; ?></title>

<link rel="stylesheet" href="/sistema/libs/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="/sistema/libs/adminlte/dist/css/adminlte.min.css">
<link rel="stylesheet" href="/sistema/libs/icons/bootstrap-icons.css">

<script src="/sistema/libs/jquery/jquery-3.7.1.min.js"></script>
<script src="/sistema/libs/chartjs/chart.umd.min.js"></script>

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

<!-- NAVBAR -->
<nav class="app-header navbar navbar-expand bg-body">
<div class="container-fluid">

<ul class="navbar-nav">
<li class="nav-item">
<a class="nav-link" data-lte-toggle="sidebar">
<i class="bi bi-list"></i>
</a>
</li>

<li class="nav-item">
<span class="nav-link">Sistema Logístico</span>
</li>
</ul>

<ul class="navbar-nav ms-auto">

<!-- 🔔 NOTIFICACIONES -->
<li class="nav-item dropdown">
<a class="nav-link" data-bs-toggle="dropdown">
<i class="bi bi-bell"></i>
<span class="badge bg-danger" id="notif-count">0</span>
</a>

<div class="dropdown-menu dropdown-menu-lg dropdown-menu-end"
     id="notif-list"
     style="max-height:300px;overflow:auto;">
<span class="dropdown-item">Cargando...</span>
</div>
</li>

<!-- 👤 USUARIO -->
<li class="nav-item dropdown">
<a class="nav-link d-flex align-items-center" data-bs-toggle="dropdown">
<img src="/sistema/img/user.png" class="rounded-circle me-2" width="30">
<span><?php echo $_SESSION['nombre']; ?></span>
</a>

<div class="dropdown-menu dropdown-menu-end">
<span class="dropdown-item-text">
Rol: <?php echo $rol; ?>
</span>

<div class="dropdown-divider"></div>

<a href="/sistema/login/logout.php" class="dropdown-item text-danger">
<i class="bi bi-box-arrow-right"></i> Cerrar sesión
</a>
</div>
</li>

</ul>

</div>
</nav>

<!-- SIDEBAR -->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">

<div class="sidebar-brand text-center py-3">
LOGÍSTICA
</div>

<div class="sidebar-wrapper">
<ul class="nav sidebar-menu flex-column">

<li class="nav-item">
<a href="/sistema/dashboard/" class="nav-link">
<i class="nav-icon bi bi-speedometer"></i>
<p>Dashboard</p>
</a>
</li>

<li class="nav-item">
<a href="/sistema/tareas/" class="nav-link">
<i class="nav-icon bi bi-list-task"></i>
<p>Sistema de tareas</p>
</a>
</li>

<li class="nav-item">
<a href="/sistema/tareas/calendario.php" class="nav-link">
<i class="nav-icon bi bi-calendar"></i>
<p>Calendario</p>
</a>
</li>

<li class="nav-item">
<a href="/sistema/tareas/historial_global.php" class="nav-link">
<i class="nav-icon bi bi-clock-history"></i>
<p>Historial</p>
</a>
</li>

<li class="nav-item">
<a href="/sistema/cubicaje/" class="nav-link">
<i class="nav-icon bi bi-box"></i>
<p>Cubicaje</p>
</a>
</li>

<li class="nav-item">
<a href="/sistema/pantalla/" class="nav-link">
<i class="nav-icon bi bi-display"></i>
<p>Pantalla</p>
</a>
</li>

<?php if ($rol === 'ADMIN') { ?>

<li class="nav-item">
<a href="/sistema/usuarios/" class="nav-link">
<i class="nav-icon bi bi-people"></i>
<p>Usuarios</p>
</a>
</li>

<li class="nav-item">
<a href="/sistema/usuarios/permisos.php" class="nav-link">
<i class="nav-icon bi bi-shield-lock"></i>
<p>Permisos</p>
</a>
</li>

<?php } ?>

</ul>
</div>

</aside>

<!-- CONTENIDO -->
<main class="app-main">
<div class="container-fluid">

<?php echo $contenido ?? ''; ?>

</div>
</main>

<footer class="app-footer text-center">
Sistema Logístico © 2026
</footer>

</div>

<script src="/sistema/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/sistema/libs/adminlte/dist/js/adminlte.min.js"></script>

<script>
// =========================
// 🔔 CARGAR NOTIFICACIONES
// =========================
function cargarNotificaciones(){

fetch('/sistema/api/notificaciones.php')
.then(r => r.json())
.then(data => {

let lista = document.getElementById('notif-list');
let badge = document.getElementById('notif-count');

lista.innerHTML = '';

let noLeidas = 0;

if(data.length === 0){
lista.innerHTML = '<span class="dropdown-item">Sin notificaciones</span>';
return;
}

data.forEach(n => {

if(n.leido == 0) noLeidas++;

let bg = n.leido == 0 ? 'style="background:#f5f5f5;"' : '';

let item = `
<a href="${n.link}" class="dropdown-item text-wrap noti-item" data-id="${n.id}" ${bg}>
${n.mensaje}
</a>
`;

lista.innerHTML += item;

});

// contador
if(noLeidas > 0){
badge.innerText = noLeidas;
}else{
badge.style.display = 'none';
}

// click marcar leída
document.querySelectorAll(".noti-item").forEach(el => {

el.addEventListener("click", function(e){

e.preventDefault();

let id = this.dataset.id;
let link = this.href;

fetch('/sistema/notificaciones/marcar_leidas.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'id='+id
})
.then(() => {
window.location.href = link;
});

});

});

});
}

// cargar al iniciar
cargarNotificaciones();

// auto refresco cada 10 segundos
setInterval(cargarNotificaciones, 10000);

</script>

</body>
</html>