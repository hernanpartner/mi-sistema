<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$id = (int)($_POST['id'] ?? 0);

if(!$id){
    echo json_encode(['ok'=>false,'error'=>'ID inválido']);
    exit;
}

try{

    // 🔥 Verificar si todas las tareas están terminadas
    $stmt = $db->prepare("
        SELECT COUNT(*) total,
        SUM(CASE WHEN estado='TERMINADO' THEN 1 ELSE 0 END) terminadas
        FROM tareas
        WHERE servicio_id=?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if($data['total'] == 0 || $data['total'] != $data['terminadas']){
        echo json_encode(['ok'=>false,'error'=>'Aún hay tareas pendientes']);
        exit;
    }

    // 🔥 GUARDAR ESTADO DEL SERVICIO
    $stmt = $db->prepare("
        UPDATE servicios 
        SET estado = 'FINALIZADO'
        WHERE id = ?
    ");
    $stmt->execute([$id]);

    echo json_encode(['ok'=>true]);

}catch(Exception $e){
    echo json_encode(['ok'=>false,'error'=>'Error']);
}