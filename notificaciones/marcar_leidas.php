<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

header('Content-Type: application/json');

Auth::verificar();

$db = Database::conectar();
$usuario_id = $_SESSION['usuario_id'];

try {

    /* =========================
       UNA NOTIFICACION
    ========================= */
    if(isset($_POST['id'])){

        $id = (int)$_POST['id'];

        if($id <= 0){
            echo json_encode(["ok" => false, "error" => "ID inválido"]);
            exit;
        }

        $stmt = $db->prepare("
            UPDATE notificaciones 
            SET leido = 1 
            WHERE id = ? AND usuario_id = ?
        ");

        $stmt->execute([$id, $usuario_id]);

        echo json_encode(["ok" => true]);
        exit;
    }

    /* =========================
       TODAS
    ========================= */
    $stmt = $db->prepare("
        UPDATE notificaciones 
        SET leido = 1 
        WHERE usuario_id = ?
    ");

    $stmt->execute([$usuario_id]);

    echo json_encode(["ok" => true]);

} catch (Exception $e){

    echo json_encode([
        "ok" => false,
        "error" => "Error interno"
    ]);
}