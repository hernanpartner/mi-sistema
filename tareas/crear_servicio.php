<?php

require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

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

/* ORDENAR EN PHP */
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
   GUARDAR
========================= */

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $codigo = $_POST['codigo'] ?? '';
    $cliente = $_POST['cliente'] ?? '';
    $origen = $_POST['origen'] ?? '';
    $destino = $_POST['destino'] ?? '';
    $etd = $_POST['etd'] ?? null;
    $eta = $_POST['eta'] ?? null;
    $categoria_id = $_POST['categoria_id'] ?? null;

    if(!$codigo || !$cliente){

        if($esAjax){
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Datos incompletos']);
            exit;
        }
    }

    try{

        $stmt = $db->prepare("
            INSERT INTO servicios
            (codigo,cliente,origen,destino,etd,eta,categoria_id)
            VALUES (?,?,?,?,?,?,?)
        ");

        $stmt->execute([
            $codigo,$cliente,$origen,$destino,$etd,$eta,$categoria_id
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
            echo json_encode(['ok'=>false,'error'=>'Error al crear']);
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
<h3>Nuevo Servicio</h3>

<a href="index.php" class="btn btn-secondary">⬅ Volver</a>
</div>

<form id="formCrearServicio">

<input name="codigo" placeholder="Código" class="form-control mb-2" required>
<input name="cliente" placeholder="Cliente" class="form-control mb-2" required>
<input name="origen" placeholder="Origen" class="form-control mb-2">
<input name="destino" placeholder="Destino" class="form-control mb-2">

<input type="datetime-local" name="etd" class="form-control mb-2">
<input type="datetime-local" name="eta" class="form-control mb-2">

<!-- 🔥 SELECT DESPLEGABLE ORDENADO -->
<label>Categoría</label>
<select name="categoria_id" class="form-select mb-3" required>

<option value="">-- Seleccionar categoría --</option>

<?php foreach($categoriasDB as $c){ ?>
<option value="<?php echo $c['id']; ?>">
<?php echo htmlspecialchars($c['nombre']); ?>
</option>
<?php } ?>

</select>

<button class="btn btn-primary">Guardar</button>

</form>

<script>

document.getElementById('formCrearServicio').addEventListener('submit', async e=>{
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
$titulo = "Crear Servicio";
require_once "../layouts/app.php";
?>