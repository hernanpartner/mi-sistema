<?php
require_once "../config/database.php";
require_once "../login/Auth.php";

Auth::verificar();

$db = Database::conectar();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("
    SELECT t.*, u.nombre as responsable, s.codigo
    FROM tareas t
    LEFT JOIN usuarios u ON t.responsable_id = u.id
    LEFT JOIN servicios s ON t.servicio_id = s.id
    WHERE t.id = ?
");
$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$t){
    echo "Tarea no encontrada";
    exit;
}
?>

<h4>[<?php echo $t['codigo']; ?>] <?php echo htmlspecialchars($t['titulo']); ?></h4>

<p><b>Descripción:</b><br><?php echo nl2br(htmlspecialchars($t['descripcion'])); ?></p>

<p><b>Responsable:</b> <?php echo htmlspecialchars($t['responsable'] ?? 'Sin asignar'); ?></p>

<p><b>Estado:</b> 
    <span id="estadoActual"><?php echo $t['estado']; ?></span>
</p>

<p><b>Prioridad:</b> <?php echo $t['prioridad']; ?></p>
<p><b>Fecha límite:</b> <?php echo $t['fecha_limite']; ?></p>

<hr>

<div class="d-flex gap-2">

<?php if($t['estado'] != 'TERMINADO'){ ?>
<button onclick="cambiarEstadoModal(<?php echo $t['id']; ?>,'EN PROCESO')" class="btn btn-info btn-sm">▶ En proceso</button>
<button onclick="cambiarEstadoModal(<?php echo $t['id']; ?>,'BLOQUEADO')" class="btn btn-warning btn-sm">⛔ Bloqueado</button>
<button onclick="cambiarEstadoModal(<?php echo $t['id']; ?>,'TERMINADO')" class="btn btn-success btn-sm">✔ Terminado</button>
<?php } else { ?>
<span class="badge bg-success">FINALIZADO</span>
<?php } ?>

</div>

<hr>

<a href="ver_servicio.php?id=<?php echo $t['servicio_id']; ?>&tarea=<?php echo $t['id']; ?>" class="btn btn-primary">
Ver completo
</a>

<script>
async function cambiarEstadoModal(id, estado){

    try{

        let res = await fetch('/sistema/tareas/cambiar_estado.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({id, estado})
        });

        let data = await res.json();

        if(data.ok){

            document.getElementById('estadoActual').innerText = estado;

            // 🔥 refresca calendario sin recargar
            if(window.calendar){
                window.calendar.refetchEvents();
            }

        }else{
            alert(data.error || 'Error');
        }

    }catch(e){
        alert('Error de conexión');
    }

}
</script>