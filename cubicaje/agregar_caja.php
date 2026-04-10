<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();
$db = Database::conectar();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $stmt = $db->prepare("
        INSERT INTO cubicaje 
        (proyecto_id,nombre,largo,ancho,alto,peso,cantidad,color,apilable,rotable)
        VALUES (?,?,?,?,?,?,?,?,1,1)
    ");

    $stmt->execute([
        $_POST['proyecto_id'],
        $_POST['nombre'],
        $_POST['largo'],
        $_POST['ancho'],
        $_POST['alto'],
        $_POST['peso'],
        $_POST['cantidad'],
        $_POST['color']
    ]);

    echo json_encode([
        'ok'=>true,
        'id'=>$db->lastInsertId()
    ]);
}