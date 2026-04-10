<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$contenedores = $db->query("SELECT * FROM contenedores")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['contenedor'] = [
        'largo' => $_POST['largo'],
        'ancho' => $_POST['ancho'],
        'alto' => $_POST['alto'],
        'puerta_ancho' => $_POST['puerta_ancho'],
        'puerta_alto' => $_POST['puerta_alto']
    ];

    header("Location: index.php");
    exit();
}
?>

<h2>Seleccionar Contenedor (cm)</h2>

<form method="POST">

<select onchange="llenar(this)">
<option value="">Seleccionar estándar</option>

<?php foreach($contenedores as $c){ ?>

<option value="<?php echo implode('|', [
$c['largo'], $c['ancho'], $c['alto'],
$c['puerta_ancho'], $c['puerta_alto']
]); ?>">
<?php echo $c['nombre']; ?>
</option>

<?php } ?>

</select>

<br><br>

Largo (cm):<br>
<input name="largo" id="largo" required><br><br>

Ancho (cm):<br>
<input name="ancho" id="ancho" required><br><br>

Alto (cm):<br>
<input name="alto" id="alto" required><br><br>

Ancho puerta (cm):<br>
<input name="puerta_ancho" id="puerta_ancho" required><br><br>

Alto puerta (cm):<br>
<input name="puerta_alto" id="puerta_alto" required><br><br>

<button>Guardar contenedor</button>

</form>

<script>
function llenar(select){
    if(select.value){
        let v = select.value.split('|');
        document.getElementById('largo').value = v[0];
        document.getElementById('ancho').value = v[1];
        document.getElementById('alto').value = v[2];
        document.getElementById('puerta_ancho').value = v[3];
        document.getElementById('puerta_alto').value = v[4];
    }
}
</script>

<br>
<a href="index.php">Volver</a>