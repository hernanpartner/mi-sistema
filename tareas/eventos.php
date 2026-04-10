<?php
require_once "../config/database.php";
require_once "../login/Auth.php";

Auth::verificar();

header('Content-Type: application/json');

try {

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
        LEFT JOIN servicios s ON t.servicio_id = s.id
        LEFT JOIN usuarios u ON t.responsable_id = u.id
    ");

    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $eventos = [];

    foreach ($tareas as $t) {

        // 🔴 SI NO HAY FECHA → NO SE MUESTRA
        if (empty($t['fecha_limite'])) {
            continue;
        }

        $hoy = date('Y-m-d');
        $atrasado = ($t['estado'] != 'TERMINADO' && $t['fecha_limite'] < $hoy);

        // colores
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

        $codigo = $t['codigo'] ?? 'SIN-COD';

        $eventos[] = [
            'id'    => $t['id'],
            'title' => "[".$codigo."] ".$t['titulo'],
            'start' => date('Y-m-d', strtotime($t['fecha_limite'])),
            'color' => $color,

            'extendedProps' => [
                'estado'       => $t['estado'],
                'prioridad'    => $t['prioridad'],
                'responsable'  => $t['responsable'],
                'fecha'        => $t['fecha_limite'],
                'servicio_id'  => $t['servicio_id']
            ]
        ];
    }

    echo json_encode($eventos);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ]);
}