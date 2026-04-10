<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
$db = Database::conectar();

$titulo = "Proyecto Cubicaje";

$proyecto_id = $_GET['id'] ?? $_SESSION['proyecto_id'] ?? 0;
$_SESSION['proyecto_id'] = $proyecto_id;

/* =========================
   CONFIG CONTENEDOR
========================= */
if(isset($_POST['guardar_contenedor'])){
    $_SESSION['contenedor'] = [
        'largo'=>$_POST['largo'],
        'ancho'=>$_POST['ancho'],
        'alto'=>$_POST['alto'],
        'peso_max'=>$_POST['peso_max'],
        'modo'=>$_POST['modo']
    ];
    header("Location: proyecto.php?id=".$proyecto_id);
    exit;
}

$tipos = [
"20" => ['largo'=>589,'ancho'=>235,'alto'=>239,'peso_max'=>28000],
"40" => ['largo'=>1200,'ancho'=>235,'alto'=>239,'peso_max'=>30000],
"40hc" => ['largo'=>1200,'ancho'=>235,'alto'=>269,'peso_max'=>30000]
];

if(isset($_POST['tipo'])){
    if(isset($tipos[$_POST['tipo']])){
        $_SESSION['contenedor'] = array_merge($tipos[$_POST['tipo']],['modo'=>'fondo']);
    }
    header("Location: proyecto.php?id=".$proyecto_id);
    exit;
}

$contenedor = $_SESSION['contenedor'] ?? [
'largo'=>1200,'ancho'=>235,'alto'=>239,'peso_max'=>30000,'modo'=>'fondo'
];

/* =========================
   TOGGLE
========================= */
if(isset($_POST['toggle'])){
    $stmt = $db->prepare("UPDATE cubicaje SET ".$_POST['campo']."=? WHERE id=?");
    $stmt->execute([$_POST['valor'], $_POST['id']]);
    exit;
}

/* =========================
   DATOS
========================= */
$stmt = $db->prepare("SELECT * FROM cubicaje WHERE proyecto_id=?");
$stmt->execute([$proyecto_id]);
$cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   TOTALES
========================= */
$totalPeso=0; $totalCajas=0; $totalVolumen=0;

foreach($cajas as $c){
    $totalPeso += $c['peso']*$c['cantidad'];
    $totalCajas += $c['cantidad'];
    $totalVolumen += ($c['largo']*$c['ancho']*$c['alto'])*$c['cantidad'];
}

/* COLORES */
$colores = [
"#FF5733","#33FF57","#3357FF","#F1C40F","#9B59B6","#1ABC9C",
"#E67E22","#E74C3C","#2ECC71","#3498DB","#34495E","#16A085",
"#27AE60","#2980B9","#8E44AD","#2C3E50","#F39C12","#D35400",
"#C0392B","#BDC3C7","#7F8C8D","#95A5A6","#FF33A8","#33FFF6",
"#A833FF","#FF8F33","#33FF8F","#8FFF33","#FF3333","#33A8FF"
];

ob_start();
?>

<div class="d-flex gap-2 mb-3">
<a href="index.php" class="btn btn-secondary">⬅ Volver</a>

<?php if(isset($_SESSION['contenedor'])): ?>
<a href="simulador3d.php" class="btn btn-primary">🚀 Simular</a>
<?php endif; ?>

<a href="../export/pdf_proyecto.php?id=<?= $proyecto_id ?>" class="btn btn-danger" target="_blank">PDF</a>
<a href="../export/excel_proyecto.php?id=<?= $proyecto_id ?>" class="btn btn-success">Excel</a>
<a href="../export/word_proyecto.php?id=<?= $proyecto_id ?>" class="btn btn-primary">Word</a>
</div>

<!-- CONTENEDOR -->
<div class="card mb-3">
<div class="card-header fw-bold">📦 Configuración Contenedor</div>
<div class="card-body">

<form method="POST" class="row g-2">

<div class="col-md-3">
<label>Tipo</label>
<select name="tipo" class="form-select" onchange="this.form.submit()">
<option value="">Seleccionar</option>
<option value="20">20 pies</option>
<option value="40">40 pies</option>
<option value="40hc">40 HC</option>
</select>
</div>

<div class="col-md-2"><label>Largo</label><input name="largo" value="<?= $contenedor['largo'] ?>" class="form-control"></div>
<div class="col-md-2"><label>Ancho</label><input name="ancho" value="<?= $contenedor['ancho'] ?>" class="form-control"></div>
<div class="col-md-2"><label>Alto</label><input name="alto" value="<?= $contenedor['alto'] ?>" class="form-control"></div>
<div class="col-md-2"><label>Peso Máximo</label><input name="peso_max" value="<?= $contenedor['peso_max'] ?>" class="form-control"></div>

<div class="col-md-2">
<label>Modo</label>
<select name="modo" class="form-select">
<option value="fondo">Fondo</option>
<option value="centro">Centro</option>
<option value="frente">Frente</option>
</select>
</div>

<div class="col-md-2 align-self-end">
<button name="guardar_contenedor" class="btn btn-success w-100">Guardar</button>
</div>

</form>

</div>
</div>

<!-- AGREGAR CAJA (AHORA AJAX) -->
<div class="card mb-3">
<div class="card-header fw-bold">📦 Agregar Caja</div>
<div class="card-body">

<form id="formCaja" class="row g-2">

<input type="hidden" name="proyecto_id" value="<?= $proyecto_id ?>">

<div class="col-md-2"><label>Nombre</label><input name="nombre" class="form-control" required></div>
<div class="col-md-1"><label>Largo</label><input name="largo" type="number" class="form-control" required></div>
<div class="col-md-1"><label>Ancho</label><input name="ancho" type="number" class="form-control" required></div>
<div class="col-md-1"><label>Alto</label><input name="alto" type="number" class="form-control" required></div>
<div class="col-md-1"><label>Peso</label><input name="peso" type="number" class="form-control" required></div>
<div class="col-md-1"><label>Cantidad</label><input name="cantidad" type="number" class="form-control" required></div>

<div class="col-md-3">
<label>Color</label>
<select name="color" class="form-select">
<?php foreach($colores as $c): ?>
<option value="<?= $c ?>" style="background:<?= $c ?>;">&nbsp;</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-2 align-self-end">
<button type="submit" class="btn btn-primary w-100">Agregar</button>
</div>

</form>

</div>
</div>

<!-- TABLA -->
<div class="card">
<div class="card-header fw-bold">📊 Listado de Cajas</div>
<div class="card-body table-responsive">

<table class="table table-bordered text-center">

<thead class="table-light">
<tr>
<th>Nombre</th>
<th>Largo</th>
<th>Ancho</th>
<th>Alto</th>
<th>Peso</th>
<th>Cantidad</th>
<th>Color</th>

<th>
Apilado<br>
<input type="checkbox" id="all_apila">
</th>

<th>
Rotación<br>
<input type="checkbox" id="all_rota">
</th>

<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach($cajas as $c): ?>
<tr id="fila_<?= $c['id'] ?>">

<td><?= $c['nombre'] ?></td>
<td><?= $c['largo'] ?></td>
<td><?= $c['ancho'] ?></td>
<td><?= $c['alto'] ?></td>
<td><?= $c['peso'] ?></td>
<td><?= $c['cantidad'] ?></td>

<td style="background:<?= $c['color'] ?>"></td>

<td><input type="checkbox" class="apila" data-id="<?= $c['id'] ?>" <?= $c['apilable']?'checked':'' ?>></td>
<td><input type="checkbox" class="rota" data-id="<?= $c['id'] ?>" <?= $c['rotable']?'checked':'' ?>></td>

<td>
<a href="editar_caja.php?id=<?= $c['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
<button class="btn btn-danger btn-sm eliminar" data-id="<?= $c['id'] ?>">🗑</button>
</td>

</tr>
<?php endforeach; ?>

</tbody>
</table>

<hr>

<b>Peso:</b> <?= $totalPeso ?> kg |
<b>Cajas:</b> <?= $totalCajas ?> |
<b>Volumen:</b> <?= number_format($totalVolumen) ?>

</div>
</div>

<script>

// AGREGAR CAJA AJAX
document.getElementById('formCaja').addEventListener('submit', function(e){
e.preventDefault();

let formData = new FormData(this);

fetch('agregar_caja.php',{
method:'POST',
body: formData
})
.then(r=>r.json())
.then(res=>{
if(res.ok){
location.reload();
}
});

});

// ELIMINAR
document.querySelectorAll('.eliminar').forEach(btn=>{
btn.addEventListener('click',function(){

let id = this.dataset.id;
if(!confirm('¿Eliminar caja?')) return;

fetch('eliminar_caja.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'id='+id
})
.then(r=>r.json())
.then(res=>{
if(res.ok){
document.getElementById('fila_'+id).remove();
}
});

});
});

// TOGGLE
document.querySelectorAll('.apila, .rota').forEach(el=>{
el.addEventListener('change',function(){

let campo = this.classList.contains('apila') ? 'apilable' : 'rotable';

fetch('',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'toggle=1&id='+this.dataset.id+'&campo='+campo+'&valor='+(this.checked?1:0)
});

});
});

// SELECT ALL
document.getElementById('all_apila').addEventListener('change',function(){
document.querySelectorAll('.apila').forEach(el=>{
el.checked = this.checked;
el.dispatchEvent(new Event('change'));
});
});

document.getElementById('all_rota').addEventListener('change',function(){
document.querySelectorAll('.rota').forEach(el=>{
el.checked = this.checked;
el.dispatchEvent(new Event('change'));
});
});

</script>

<?php
$contenido = ob_get_clean();
require_once "../layouts/app.php";
?>