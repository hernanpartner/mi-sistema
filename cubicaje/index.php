<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
$db = Database::conectar();

$titulo = "Proyectos Cubicaje";

$rol = Auth::rol();

/* LISTAR */
$proyectos = $db->query("SELECT * FROM proyectos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="card mb-3">
<div class="card-body">

<?php if($rol === 'ADMIN'): ?>

<form id="formCrearProyecto" class="row g-2">
<div class="col-md-4">
<input type="text" name="nombre" id="nombreProyecto" class="form-control" placeholder="Nombre proyecto" required maxlength="100">
</div>
<div class="col-md-2">
<button class="btn btn-primary">Crear</button>
</div>
</form>

<?php endif; ?>

</div>
</div>

<div class="card">
<div class="card-header fw-bold">📦 Proyectos</div>
<div class="card-body table-responsive">

<table class="table table-bordered text-center">

<thead class="table-light">
<tr>
<th>Nombre</th>
<th>Fecha</th>
<th>Acciones</th>
</tr>
</thead>

<tbody id="tablaProyectos">

<?php foreach($proyectos as $p): 
    $id = (int)$p['id'];
    $nombre = htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8');
?>

<tr id="fila_proyecto_<?php echo $id; ?>">

<td><?php echo $nombre; ?></td>
<td><?php echo htmlspecialchars($p['fecha']); ?></td>

<td>

<a href="proyecto.php?id=<?php echo $id; ?>" class="btn btn-info btn-sm">Ver</a>

<?php if($rol === 'ADMIN'): ?>

<a href="editar_proyecto.php?id=<?php echo $id; ?>" class="btn btn-warning btn-sm">✏️</a>

<button class="btn btn-danger btn-sm btnEliminarProyecto"
data-id="<?php echo $id; ?>"
data-nombre="<?php echo $nombre; ?>">
🗑
</button>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalEliminarProyecto">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header bg-danger text-white">
<h5 class="modal-title">Eliminar proyecto</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body" id="textoEliminarProyecto"></div>

<div class="modal-footer">
<button class="btn btn-success" data-bs-dismiss="modal">Cancelar</button>
<button class="btn btn-danger" id="btnEliminarProyecto">Eliminar</button>
</div>

</div>
</div>
</div>

<script>

// 🔥 CREAR PROYECTO AJAX
$('#formCrearProyecto').submit(function(e){
    e.preventDefault();

    let nombre = $('#nombreProyecto').val().trim();

    if(nombre.length < 2){
        alert("Nombre demasiado corto");
        return;
    }

    $.post('crear_proyecto.php', {nombre: nombre}, function(res){

        if(res.ok){

            let p = res.proyecto;
            let nombreSafe = $('<div>').text(p.nombre).html();

            let fila = `
            <tr id="fila_proyecto_${p.id}">
                <td>${nombreSafe}</td>
                <td>${p.fecha}</td>
                <td>
                    <a href="proyecto.php?id=${p.id}" class="btn btn-info btn-sm">Ver</a>
                    <a href="editar_proyecto.php?id=${p.id}" class="btn btn-warning btn-sm">✏️</a>
                    <button class="btn btn-danger btn-sm btnEliminarProyecto"
                    data-id="${p.id}"
                    data-nombre="${nombreSafe}">
                    🗑
                    </button>
                </td>
            </tr>`;

            $('#tablaProyectos').prepend(fila);
            $('#nombreProyecto').val('');
            mostrarToast("Proyecto creado");

        }else{
            alert(res.error || 'Error');
        }

    }, 'json');

});

// 🔥 CLICK ELIMINAR (EVENTO DELEGADO)
let proyectoId = 0;

$(document).on('click', '.btnEliminarProyecto', function(){

    proyectoId = $(this).data('id');
    let nombre = $(this).data('nombre');

    $('#textoEliminarProyecto').html("¿Eliminar el proyecto <b>"+nombre+"</b>?");

    new bootstrap.Modal(document.getElementById('modalEliminarProyecto')).show();
});

$('#btnEliminarProyecto').click(function(){

    $.post('eliminar_proyecto.php', {id: proyectoId}, function(res){

        if(res.ok){
            $('#fila_proyecto_'+proyectoId).remove();
            mostrarToast("Proyecto eliminado");
            $('.modal').modal('hide');
        }else{
            alert(res.error || 'Error al eliminar');
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