<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$id = $_GET['id'] ?? 0;

/* =========================
   OBTENER SERVICIO
========================= */

$stmt = $db->prepare("SELECT * FROM servicios WHERE id=?");
$stmt->execute([$id]);
$s = $stmt->fetch();

if(!$s){
    die("Servicio no encontrado");
}

/* =========================
   ORDEN PERSONALIZADO
========================= */

$ordenCategorias = [
    'areas',
    'maritimas',
    'terrestres',
    'seguro',
    'otros',
    'servicios'
];

/* =========================
   OBTENER CATEGORIAS
========================= */

$categoriasDB = $db->query("SELECT * FROM categorias")->fetchAll();

/* ORDENAR */
usort($categoriasDB, function($a, $b) use ($ordenCategorias) {

    $posA = array_search(strtolower($a['nombre']), $ordenCategorias);
    $posB = array_search(strtolower($b['nombre']), $ordenCategorias);

    $posA = $posA === false ? 999 : $posA;
    $posB = $posB === false ? 999 : $posB;

    return $posA - $posB;
});

/* =========================
   DETECTAR AJAX
========================= */

$esAjax = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
);

/* =========================
   ACTUALIZAR
========================= */

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    try{

        $stmt = $db->prepare("
            UPDATE servicios SET
            codigo=?,cliente=?,origen=?,destino=?,etd=?,eta=?,categoria_id=?
            WHERE id=?
        ");

        $stmt->execute([
            $_POST['codigo'],
            $_POST['cliente'],
            $_POST['origen'],
            $_POST['destino'],
            $_POST['etd'],
            $_POST['eta'],
            $_POST['categoria_id'],
            $id
        ]);

        if($esAjax){
            header('Content-Type: application/json');
            echo json_encode(['ok'=>true]);
            exit;
        }

        header("Location: index.php");
        exit;

    }catch(Exception $e){

        if($esAjax){
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Error al actualizar']);
            exit;
        }
    }
}

/* =========================
   FORMULARIO
========================= */

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
<h3>Editar Servicio</h3>

<a href="index.php" class="btn btn-secondary">⬅ Volver</a>
</div>

<form id="formEditarServicio">

<input name="codigo" value="<?= htmlspecialchars($s['codigo']) ?>" class="form-control mb-2">
<input name="cliente" value="<?= htmlspecialchars($s['cliente']) ?>" class="form-control mb-2">
<input name="origen" value="<?= htmlspecialchars($s['origen']) ?>" class="form-control mb-2">
<input name="destino" value="<?= htmlspecialchars($s['destino']) ?>" class="form-control mb-2">

<input type="datetime-local" name="etd"
value="<?= $s['etd'] ? date('Y-m-d\TH:i',strtotime($s['etd'])) : '' ?>"
class="form-control mb-2">

<input type="datetime-local" name="eta"
value="<?= $s['eta'] ? date('Y-m-d\TH:i',strtotime($s['eta'])) : '' ?>"
class="form-control mb-2">

<!-- 🔥 SELECT CATEGORIA -->
<label>Categoría</label>
<select name="categoria_id" class="form-select mb-3" required>

<option value="">-- Seleccionar categoría --</option>

<?php foreach($categoriasDB as $c){ ?>
<option value="<?= $c['id'] ?>"
<?= ($s['categoria_id'] == $c['id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($c['nombre']) ?>
</option>
<?php } ?>

</select>

<button class="btn btn-success">Actualizar</button>

</form>

<script>

document.getElementById('formEditarServicio').addEventListener('submit', async e=>{
e.preventDefault();

let form = new FormData(e.target);

let res = await fetch('',{
method:'POST',
headers:{'X-Requested-With':'XMLHttpRequest'},
body:form
}).then(r=>r.json());

if(res.ok){
window.location.href = "index.php";
}else{
alert(res.error);
}

});

</script>

<?php
$contenido = ob_get_clean();
$titulo = "Editar Servicio";
require_once "../layouts/app.php";
?>