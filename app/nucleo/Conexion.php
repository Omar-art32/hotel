<?php

/**
 * Conexión única a MariaDB con PDO.
 * Uso: $pdo = Conexion::obtener();
 */
class Conexion
{
    private static ?PDO $pdo = null;

    public static function obtener(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PUERTO, DB_NOMBRE);
            self::$pdo = new PDO($dsn, DB_USUARIO, DB_CONTRASENA, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Consultas preparadas reales: el servidor separa la consulta de los datos.
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$pdo;
    }
}
