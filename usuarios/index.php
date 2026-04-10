<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
Auth::solo('ADMIN');

$db = Database::conectar();

$stmt = $db->query("SELECT * FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="d-flex justify-content-between mb-3">
    <h3>Gestión de Usuarios</h3>

    <button class="btn btn-primary" id="btnNuevo">
        <i class="bi bi-plus"></i> Nuevo Usuario
    </button>
</div>

<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Usuario</th>
<th>Rol</th>
<th width="180">Acciones</th>
</tr>
</thead>

<tbody id="tablaUsuarios">

<?php foreach($usuarios as $u): ?>
<tr id="fila_<?php echo $u['id']; ?>">

<td><?php echo $u['id']; ?></td>

<td class="nombre"><?php echo htmlspecialchars($u['nombre']); ?></td>
<td class="usuario"><?php echo htmlspecialchars($u['usuario']); ?></td>

<td class="rol" data-rol="<?php echo $u['rol']; ?>">
<span class="badge bg-<?php echo $u['rol']=='ADMIN' ? 'danger' : 'secondary'; ?>">
<?php echo $u['rol']; ?>
</span>
</td>

<td>

<button class="btn btn-warning btn-sm btnEditar"
data-id="<?php echo $u['id']; ?>">
<i class="bi bi-pencil"></i>
</button>

<button class="btn btn-danger btn-sm btnEliminar"
data-id="<?php echo $u['id']; ?>">
<i class="bi bi-trash"></i>
</button>

</td>

</tr>
<?php endforeach; ?>

</tbody>

</table>

</div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Usuario</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" id="id">

<div class="mb-2">
<label>Nombre</label>
<input type="text" id="nombre" class="form-control">
</div>

<div class="mb-2">
<label>Usuario</label>
<input type="text" id="usuario" class="form-control">
</div>

<div class="mb-2">
<label>Password</label>
<input type="password" id="password" class="form-control">
</div>

<div class="mb-2">
<label>Rol</label>
<select id="rol" class="form-control">
<option value="ADMIN">ADMIN</option>
<option value="USER">USER</option>
</select>
</div>

</div>

<div class="modal-footer">
<button class="btn btn-success" id="btnGuardar">Guardar</button>
</div>

</div>
</div>
</div>

<script>

/* =========================
   ESPERAR A QUE TODO CARGUE
========================= */
document.addEventListener("DOMContentLoaded", function(){

const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));

/* =========================
   NUEVO
========================= */
document.getElementById('btnNuevo').addEventListener('click', () => {

document.getElementById('id').value = '';
document.getElementById('nombre').value = '';
document.getElementById('usuario').value = '';
document.getElementById('password').value = '';
document.getElementById('rol').value = 'USER';

modal.show();

});

/* =========================
   EDITAR (DELEGACIÓN)
========================= */
document.addEventListener('click', function(e){

if(e.target.closest('.btnEditar')){

let btn = e.target.closest('.btnEditar');
let id = btn.dataset.id;

let fila = document.getElementById('fila_'+id);

document.getElementById('id').value = id;
document.getElementById('nombre').value = fila.querySelector('.nombre').innerText;
document.getElementById('usuario').value = fila.querySelector('.usuario').innerText;
document.getElementById('password').value = '';
document.getElementById('rol').value = fila.querySelector('.rol').dataset.rol;

modal.show();

}

});

/* =========================
   GUARDAR
========================= */
document.getElementById('btnGuardar').addEventListener('click', async () => {

let id = document.getElementById('id').value;

let res = await fetch(id ? 'actualizar.php' : 'guardar.php',{
method:'POST',
headers:{'Content-Type':'application/json'},
body: JSON.stringify({
id:id,
nombre:document.getElementById('nombre').value,
usuario:document.getElementById('usuario').value,
password:document.getElementById('password').value,
rol:document.getElementById('rol').value
})
});

let data = await res.json();

if(data.ok){
location.reload();
}else{
alert(data.error || 'Error');
}

});

/* =========================
   ELIMINAR (DELEGACIÓN)
========================= */
document.addEventListener('click', async function(e){

if(e.target.closest('.btnEliminar')){

let btn = e.target.closest('.btnEliminar');
let id = btn.dataset.id;

if(!confirm('¿Eliminar usuario?')) return;

let res = await fetch('eliminar_ajax.php',{
method:'POST',
headers:{'Content-Type':'application/json'},
body: JSON.stringify({id:id})
});

let data = await res.json();

if(data.ok){
document.getElementById('fila_'+id).remove();
}else{
alert(data.error || 'Error');
}

}

});

});
</script>

<?php
$contenido = ob_get_clean();
$titulo = "Usuarios";
require_once __DIR__ . "/../layouts/app.php";
?>