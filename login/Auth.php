<?php

class Auth {

    private static $timeout = 1800; // 30 minutos

    /* =========================
       ASEGURAR SESSION
    ========================= */
    private static function iniciar(){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
    }

    /* =========================
       RESPUESTA JSON SEGURA
    ========================= */
    private static function json($data){
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /* =========================
       DETECTAR AJAX
    ========================= */
    private static function esAjax(){
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }

    /* =========================
       VERIFICAR SESIÓN
    ========================= */
    public static function verificar(){

        self::iniciar();

        // AUTO LOGIN COOKIE
        if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['remember_token'])) {
            self::loginDesdeCookie();
        }

        if (!isset($_SESSION['usuario_id'])) {

            if(self::esAjax()){
                self::json(['error' => 'no_auth']);
            }

            header("Location: /sistema/login/index.php");
            exit;
        }

        // EXPIRACIÓN
        if (isset($_SESSION['ultimo_acceso']) &&
            (time() - $_SESSION['ultimo_acceso'] > self::$timeout)) {

            self::logout();

            if(self::esAjax()){
                self::json(['error' => 'session_expired']);
            }

            header("Location: /sistema/login/index.php?expirado=1");
            exit;
        }

        $_SESSION['ultimo_acceso'] = time();

        // SEGURIDAD IP + USER AGENT
        if (
            isset($_SESSION['ip'], $_SESSION['agent']) &&
            ($_SESSION['ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '') ||
             $_SESSION['agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? ''))
        ) {
            self::logout();

            if(self::esAjax()){
                self::json(['error' => 'session_invalid']);
            }

            header("Location: /sistema/login/index.php");
            exit;
        }
    }

    /* =========================
       LOGIN
    ========================= */
    public static function login($usuario, $recordar = false){

        self::iniciar();

        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = strtoupper(trim($usuario['rol'] ?? 'USER'));

        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ultimo_acceso'] = time();

        if ($recordar) {

            $token = bin2hex(random_bytes(32));

            setcookie(
                "remember_token",
                $token,
                time() + (86400 * 30),
                "/",
                "",
                false,
                true
            );

            require_once __DIR__ . "/../config/database.php";
            $db = Database::conectar();

            $stmt = $db->prepare("UPDATE usuarios SET remember_token=? WHERE id=?");
            $stmt->execute([$token, $usuario['id']]);
        }
    }

    /* =========================
       COOKIE LOGIN
    ========================= */
    private static function loginDesdeCookie(){

        require_once __DIR__ . "/../config/database.php";
        $db = Database::conectar();

        $token = $_COOKIE['remember_token'] ?? '';

        if(!$token) return;

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE remember_token=?");
        $stmt->execute([$token]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            self::login($user, false);
        }
    }

    /* =========================
       LOGOUT
    ========================= */
    public static function logout(){

        self::iniciar();

        if (isset($_COOKIE['remember_token'])) {
            setcookie("remember_token", "", time() - 3600, "/");
        }

        $_SESSION = [];
        session_destroy();
    }

    /* =========================
       OBTENER ROL
    ========================= */
    public static function rol(){

        self::iniciar();

        return strtoupper(trim($_SESSION['rol'] ?? 'USER'));
    }

    /* =========================
       SOLO ROL
    ========================= */
    public static function solo($rol){

        self::iniciar();

        $actual = strtoupper(trim($_SESSION['rol'] ?? ''));

        // 🔥 ADMIN SIEMPRE PASA (TU REGLA)
        if($actual === 'ADMIN'){
            return;
        }

        if($actual !== strtoupper($rol)){

            if(self::esAjax()){
                self::json(['error' => 'no_permission']);
            }

            die("⛔ Acceso restringido");
        }
    }

    /* =========================
       VARIOS ROLES
    ========================= */
    public static function requiere($roles = []){

        self::iniciar();

        $actual = strtoupper(trim($_SESSION['rol'] ?? ''));

        // 🔥 ADMIN SIEMPRE PASA
        if($actual === 'ADMIN'){
            return;
        }

        $roles = array_map(fn($r) => strtoupper($r), $roles);

        if(!in_array($actual, $roles)){

            if(self::esAjax()){
                self::json(['error' => 'no_permission']);
            }

            die("⛔ Sin permisos");
        }
    }
}