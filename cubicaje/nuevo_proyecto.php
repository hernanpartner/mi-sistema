<?php
require_once "../config/database.php";

if($_POST){

    $db = Database::conectar();

    $stmt = $db->prepare("INSERT INTO proyectos(nombre) VALUES(?)");
    $stmt->execute([$_POST['nombre']]);

    header("Location: index.php");
}
?>

<h3>Nuevo Proyecto</h3>

<form method="POST">
Nombre del proyecto:<br>
<input type="text" name="nombre" required><br><br>

<button>Guardar</button>
</form>