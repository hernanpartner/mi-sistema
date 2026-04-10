<?php

date_default_timezone_set('America/La_Paz');

require_once "../config/database.php";

$db = Database::conectar();

$ahora = date('Y-m-d H:i:s');

// 🔢 TOTAL
$total = $db->query("SELECT COUNT(*) FROM servicios WHERE visible_pantalla=1")->fetchColumn();

// 🟦 PROGRAMADOS
$programados = $db->query("
SELECT COUNT(*) FROM servicios 
WHERE visible_pantalla=1 
AND etd IS NOT NULL 
AND etd > '$ahora'
")->fetchColumn();

// 🟨 EN TRÁNSITO
$transito = $db->query("
SELECT COUNT(*) FROM servicios 
WHERE visible_pantalla=1 
AND etd <= '$ahora' 
AND eta >= '$ahora'
")->fetchColumn();

// 🟩 ARRIBADOS
$arribados = $db->query("
SELECT COUNT(*) FROM servicios 
WHERE visible_pantalla=1 
AND eta < '$ahora'
")->fetchColumn();

// 🚨 RETRASADOS (ETA ya pasó pero sigue activo)
$retrasados = $db->query("
SELECT COUNT(*) FROM servicios 
WHERE visible_pantalla=1 
AND eta < '$ahora'
AND estado = 'activo'
")->fetchColumn();

echo json_encode([
    "total" => $total,
    "programados" => $programados,
    "transito" => $transito,
    "arribados" => $arribados,
    "retrasados" => $retrasados
]);