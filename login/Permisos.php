<?php

require_once __DIR__ . "/Auth.php";
require_once __DIR__ . "/../config/database.php";

class Permisos
{

public static function lista()
{
    return [

        // SERVICIOS
        'servicios.ver',
        'servicios.crear',
        'servicios.editar',
        'servicios.eliminar',

        // TAREAS
        'tareas.ver',
        'tareas.crear',
        'tareas.cambiar_estado',
        'tareas.historial',

        // HISTORIAL
        'historial.ver',

        // USUARIOS
        'usuarios.ver',
        'usuarios.crear',
        'usuarios.editar',
        'usuarios.eliminar',

        // EXPORTACIONES
        'export.ver',

        // 🔥 CUBICAJE (NUEVO)
        'cubicaje.ver',
        'cubicaje.crear',
        'cubicaje.editar',
        'cubicaje.eliminar'
    ];
}

private static function rolLimpio()
{
    $rol = Auth::rol();
    return strtoupper(trim($rol));
}

private static function permisosRol($rol)
{
    $db = Database::conectar();

    $stmt = $db->prepare("SELECT permiso FROM permisos WHERE UPPER(TRIM(rol)) = ?");
    $stmt->execute([$rol]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$data) return [];

    return array_map(function($p){
        return trim($p['permiso']);
    }, $data);
}

public static function puede($permiso)
{
    $rol = self::rolLimpio();

    if ($rol === 'ADMIN') return true;

    $permisos = self::permisosRol($rol);

    return in_array(trim($permiso), $permisos);
}

public static function requerir($permiso)
{
    if (!self::puede($permiso)) {

        http_response_code(403);

        die("
        <div style='padding:20px;font-family:sans-serif'>
            <h3>⛔ Acceso denegado</h3>
            <p>No tienes permisos para esta acción.</p>
        </div>
        ");
    }
}

}