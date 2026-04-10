<?php
require_once "../config/database.php";
require_once "../login/Auth.php";

Auth::verificar();

header('Content-Type: application/json');

$db = Database::conectar();

$stmt = $db->query("
    SELECT 
        t.id,
        t.titulo,
        t.fecha_limite,
        t.estado,
        t.prioridad,
        t.servicio_id,
        u.nombre AS responsable,
        s.codigo
    FROM tareas t
    JOIN servicios s ON t.servicio_id = s.id
    LEFT JOIN usuarios u ON t.responsable_id = u.id
");

$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$eventos = [];

foreach ($tareas as $t) {

    $hoy = date('Y-m-d');
    $atrasado = ($t['estado'] != 'TERMINADO' && $t['fecha_limite'] < $hoy);

    // colores por estado
    if ($atrasado) {
        $color = '#dc3545';
    } else {
        switch ($t['estado']) {
            case 'PENDIENTE':
                $color = '#6c757d';
                break;
            case 'EN PROCESO':
                $color = '#0d6efd';
                break;
            case 'BLOQUEADO':
                $color = '#fd7e14';
                break;
            case 'TERMINADO':
                $color = '#198754';
                break;
            default:
                $color = '#6c757d';
        }
    }

    $eventos[] = [
        'id'    => $t['servicio_id'],
        'title' => "[".$t['codigo']."] ".$t['titulo'],
        'start' => $t['fecha_limite'],
        'color' => $color,

        // 🔥 datos extra para tooltip
        'extendedProps' => [
            'estado'      => $t['estado'],
            'prioridad'   => $t['prioridad'],
            'responsable' => $t['responsable'],
            'fecha'       => $t['fecha_limite']
        ]
    ];
}

echo json_encode($eventos);