<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();
$usuario_id = $_SESSION['usuario_id'];

/* =========================
   OBTENER NOTIFICACIONES
========================= */
$stmt = $db->prepare("
    SELECT n.*, s.codigo AS servicio_codigo
    FROM notificaciones n
    LEFT JOIN servicios s ON n.servicio_id = s.id
    WHERE n.usuario_id = ?
    ORDER BY n.fecha DESC
    LIMIT 20
");
$stmt->execute([$usuario_id]);

$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CONTADOR
========================= */
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM notificaciones 
    WHERE usuario_id = ? AND leido = 0
");
$stmt->execute([$usuario_id]);

$noLeidas = $stmt->fetchColumn();
?>

<li class="nav-item dropdown">
<a class="nav-link" data-bs-toggle="dropdown" href="#">
    <i class="bi bi-bell"></i>
    <?php if($noLeidas > 0){ ?>
    <span class="badge bg-danger" id="notif-count">
        <?php echo $noLeidas; ?>
    </span>
    <?php } ?>
</a>

<div class="dropdown-menu dropdown-menu-lg dropdown-menu-end" 
     id="notif-list"
     style="max-height:300px;overflow:auto;">

<?php if(!$notificaciones){ ?>
    <span class="dropdown-item">Sin notificaciones</span>
<?php } ?>

<?php foreach($notificaciones as $n){ ?>

<?php
$link = "/sistema/tareas/ver_servicio.php?id=" . $n['servicio_id'];

if (!empty($n['tarea_id'])) {
    $link .= "&tarea=" . $n['tarea_id'];
}
?>

<a href="<?php echo $link; ?>" 
   class="dropdown-item text-wrap noti-item"
   data-id="<?php echo $n['id']; ?>"
   style="<?php echo !$n['leido'] ? 'background:#f5f5f5;' : ''; ?>">

<?php if (!empty($n['servicio_codigo'])) { ?>
<strong>[<?php echo htmlspecialchars($n['servicio_codigo']); ?>]</strong>
<?php } ?>

<?php echo htmlspecialchars($n['mensaje']); ?>

<br>
<small class="text-muted">
<?php echo date("d/m H:i", strtotime($n['fecha'])); ?>
</small>

</a>

<?php } ?>

</div>
</li>

<script>

document.querySelectorAll(".noti-item").forEach(el => {

    el.addEventListener("click", function(e){

        e.preventDefault();

        let id = this.dataset.id;
        let link = this.href;

        fetch('/sistema/notificaciones/marcar_leidas.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(id)
        })
        .then(res => res.text()) // 🔥 IMPORTANTE
        .catch(() => {}) // 🔥 IGNORA ERROR
        .finally(() => {

            // 🔥 SIEMPRE REDIRIGE (CLAVE)
            let badge = document.getElementById("notif-count");

            if(badge){
                let n = parseInt(badge.innerText || 0);
                n--;

                if(n <= 0){
                    badge.remove();
                } else {
                    badge.innerText = n;
                }
            }

            window.location.href = link;

        });

    });

});

</script>