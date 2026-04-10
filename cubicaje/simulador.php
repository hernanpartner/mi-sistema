<?php
require_once "../login/Auth.php";
require_once "../config/database.php";
require_once "MotorAcomodo.php";

Auth::verificar();

$db = Database::conectar();

$contenedor = $_SESSION['contenedor'] ?? null;

if (!$contenedor) {
    die("No hay contenedor configurado");
}

$stmt = $db->query("SELECT * FROM cubicaje");
$cajasDB = $stmt->fetchAll();

$cajas = [];

foreach ($cajasDB as $c) {
    for ($i = 0; $i < $c['cantidad']; $i++) {
        $cajas[] = [
            'nombre' => $c['nombre'],
            'largo' => $c['largo'],
            'ancho' => $c['ancho'],
            'alto' => $c['alto'],
            'color' => $c['color']
        ];
    }
}

$colocadas = MotorAcomodo::acomodar($contenedor, $cajas);

// calcular escala automática
$escalaX = 900 / $contenedor['largo'];
$escalaY = 450 / $contenedor['ancho'];
$escala = min($escalaX, $escalaY);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Simulador de Carga</title>
<style>
body { font-family: Arial; }
#contenedor {
    position: relative;
    border: 3px solid black;
    margin-top: 20px;
}
.caja {
    position: absolute;
    opacity: 0.8;
    border: 1px solid #000;
    box-sizing: border-box;
}
</style>
</head>
<body>

<h2>Simulación de Carga</h2>

<p>
Contenedor:
<?php echo $contenedor['largo']; ?> x
<?php echo $contenedor['ancho']; ?> x
<?php echo $contenedor['alto']; ?> cm
</p>

<div id="contenedor"></div>

<script>
let escala = <?php echo $escala; ?>;
let contenedor = document.getElementById("contenedor");

contenedor.style.width  = (<?php echo $contenedor['largo']; ?> * escala) + "px";
contenedor.style.height = (<?php echo $contenedor['ancho']; ?> * escala) + "px";

let cajas = <?php echo json_encode($colocadas); ?>;

cajas.forEach(c => {

    let div = document.createElement("div");
    div.className = "caja";

    div.style.left = (c.x * escala) + "px";
    div.style.top  = (c.y * escala) + "px";
    div.style.width  = (c.l * escala) + "px";
    div.style.height = (c.a * escala) + "px";
    div.style.background = c.color;

    div.title = c.nombre + " (" + c.l + "x" + c.a + "x" + c.h + ")";

    contenedor.appendChild(div);
});
</script>

<br>
<a href="index.php">Volver</a>

</body>
</html>