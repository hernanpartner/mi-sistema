<?php 

date_default_timezone_set('America/La_Paz');

require_once "../config/database.php";

$db = Database::conectar();

$sql = "
SELECT 
    id,
    codigo AS servicio,
    cliente AS empresa,
    master,
    house,
    origen,
    destino,
    factura,
    etd,
    eta
FROM servicios
WHERE visible_pantalla = 1
ORDER BY etd ASC
";

$stmt = $db->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔥 CALCULAR ESTADO AUTOMÁTICO
$ahora = date('Y-m-d H:i:s');

foreach ($data as &$s) {

    // 🔧 Caso sin fechas
    if (empty($s['etd']) && empty($s['eta'])) {
        $s['estado_logistico'] = 'sin_programar';
        continue;
    }

    // 🔧 Caso normal
    if (!empty($s['etd']) && !empty($s['eta'])) {

        if ($ahora < $s['etd']) {
            $s['estado_logistico'] = 'programado';
        } elseif ($ahora >= $s['etd'] && $ahora < $s['eta']) {
            $s['estado_logistico'] = 'en_transito';
        } else {
            $s['estado_logistico'] = 'arribado';
        }

    } else {
        // 🔧 Si falta una de las fechas
        $s['estado_logistico'] = 'programado';
    }
}

header('Content-Type: application/json');
echo json_encode($data);