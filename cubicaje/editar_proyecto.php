<?php 
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
Auth::solo('ADMIN');

$db = Database::conectar();

$titulo = "Editar Proyecto";

$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM proyectos WHERE id=?");
$stmt->execute([$id]);
$proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$proyecto){
    die("Proyecto no encontrado");
}

ob_start();
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-header fw-bold">
        ✏️ Editar Proyecto
    </div>

    <div class="card-body">

        <form id="formEditarProyecto">

            <input type="hidden" id="proyectoId" value="<?php echo $proyecto['id']; ?>">

            <div class="mb-3">
                <label class="form-label">Nombre del Proyecto</label>
                <input 
                    id="nombreProyecto"
                    value="<?php echo htmlspecialchars($proyecto['nombre']); ?>" 
                    class="form-control"
                    required
                >
            </div>

            <button class="btn btn-success">
                <i class="bi bi-check-circle"></i> Guardar
            </button>

        </form>

    </div>
</div>

<script>

// 🔥 EDITAR CON AJAX
$('#formEditarProyecto').submit(function(e){
    e.preventDefault();

    let id = $('#proyectoId').val();
    let nombre = $('#nombreProyecto').val();

    $.post('actualizar_proyecto.php', {id: id, nombre: nombre}, function(res){

        if(res.ok){
            mostrarToast("Proyecto actualizado");

            setTimeout(()=>{
                window.location.href = "index.php";
            }, 1000);

        }else{
            alert(res.error);
        }

    }, 'json');

});

// 🔥 TOAST
function mostrarToast(msg){
    let toast = document.createElement('div');
    toast.className = "toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3 show";
    toast.innerHTML = "<div class='d-flex'><div class='toast-body'>"+msg+"</div></div>";
    document.body.appendChild(toast);
    setTimeout(()=>toast.remove(),3000);
}

</script>

<?php
$contenido = ob_get_clean();
require_once "../layouts/app.php";
?>