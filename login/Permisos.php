<?php

require_once __DIR__ . "/Auth.php";
require_once __DIR__ . "/../config/database.php";

class Permisos
{

    /* =========================
       LISTA GLOBAL (REFERENCIA)
    ========================= */
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

            // CUBICAJE
            'cubicaje.ver',
            'cubicaje.crear',
            'cubicaje.editar',
            'cubicaje.eliminar'
        ];
    }

    /* =========================
       ROL NORMALIZADO
    ========================= */
    private static function rol()
    {
        return strtoupper(trim(Auth::rol()));
    }

    /* =========================
       OBTENER PERMISOS (CACHE)
    ========================= */
    private static function permisos()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 🔥 CACHE EN SESIÓN
        if (isset($_SESSION['permisos'])) {
            return $_SESSION['permisos'];
        }

        $rol = self::rol();

        $db = Database::conectar();

        $stmt = $db->prepare("
            SELECT permiso 
            FROM permisos 
            WHERE UPPER(TRIM(rol)) = ?
        ");
        $stmt->execute([$rol]);

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $permisos = array_map(function($p){
            return trim($p['permiso']);
        }, $data);

        // 🔥 GUARDAR EN SESIÓN
        $_SESSION['permisos'] = $permisos;

        return $permisos;
    }

    /* =========================
       VERIFICAR PERMISO
    ========================= */
    public static function puede($permiso)
    {
        $rol = self::rol();

        // 🔥 ADMIN TOTAL
        if ($rol === 'ADMIN') return true;

        $permisos = self::permisos();

        // 🔥 SOPORTE PARA "*"
        if (in_array('*', $permisos)) return true;

        return in_array(trim($permiso), $permisos);
    }

    /* =========================
       OBLIGAR PERMISO
    ========================= */
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

    /* =========================
       LIMPIAR CACHE (IMPORTANTE)
    ========================= */
    public static function limpiarCache()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['permisos']);
    }

}