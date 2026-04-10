<?php 
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

if (session_status() === PHP_SESSION_NONE) session_start();

$db = Database::conectar();

$titulo = "Editar Caja";

$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM cubicaje WHERE id=?");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$c){
    die("Caja no encontrada");
}

/* 🔥 AJAX EDITAR */
if(isset($_POST['ajax'])){

    $stmt = $db->prepare("UPDATE cubicaje SET 
        nombre=?, largo=?, ancho=?, alto=?, peso=?, cantidad=?, color=? 
        WHERE id=?");

    $stmt->execute([
        $_POST['nombre'],
        $_POST['largo'],
        $_POST['ancho'],
        $_POST['alto'],
        $_POST['peso'],
        $_POST['cantidad'],
        $_POST['color'],
        $id
    ]);

    echo json_encode(["ok"=>true]);
    exit;
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

<div class="mb-3">
    <a href="proyecto.php?id=<?php echo $_SESSION['proyecto_id']; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-header fw-bold">
        📦 Editar Caja
    </div>

    <div class="card-body">

        <form id="formEditarCaja" class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input name="nombre" value="<?php echo $c['nombre']; ?>" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Largo</label>
                <input name="largo" type="number" value="<?php echo $c['largo']; ?>" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Ancho</label>
                <input name="ancho" type="number" value="<?php echo $c['ancho']; ?>" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Alto</label>
                <input name="alto" type="number" value="<?php echo $c['alto']; ?>" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Peso</label>
                <input name="peso" type="number" value="<?php echo $c['peso']; ?>" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input name="cantidad" type="number" value="<?php echo $c['cantidad']; ?>" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Color</label>
                <select name="color" class="form-select">
                    <?php foreach($colores as $color): ?>
                        <option 
                            value="<?php echo $color; ?>" 
                            style="background:<?php echo $color; ?>;"
                            <?php if($color == $c['color']) echo 'selected'; ?>
                        ></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <button class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Guardar cambios
                </button>
            </div>

        </form>

    </div>
</div>

<script>

document.getElementById('formEditarCaja').addEventListener('submit', function(e){
    e.preventDefault();

    let form = new FormData(this);
    form.append('ajax', 1);

    fetch('',{
        method:'POST',
        body: form
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.ok){
            window.location.href = "proyecto.php?id=<?php echo $_SESSION['proyecto_id']; ?>";
        }
    });
});

</script>

<?php
$contenido = ob_get_clean();
require_once "../layouts/app.php";
?>