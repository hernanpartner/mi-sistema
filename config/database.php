<?php

class Database {

    private static $conexion = null;

    public static function conectar() {

        if (self::$conexion === null) {

            try {

                self::$conexion = new PDO(
                    "mysql:host=localhost;dbname=sistema_pro;charset=utf8",
                    "root",
                    ""
                );

                // 🔥 CONFIGURACIONES IMPORTANTES
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            } catch (PDOException $e) {

                // ❌ NO mostrar error al usuario
                // ✔ Guardar error en log
                error_log("Error de conexión: " . $e->getMessage());

                // ✔ Mensaje seguro
                die("Error al conectar con la base de datos. Contacta al administrador.");

            }
        }

        return self::$conexion;
    }
}