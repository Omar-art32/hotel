<?php

/**
 * Sesión de PHP con cookie segura y cierre por inactividad.
 * El usuario autenticado se guarda en $_SESSION['usuario'].
 */
class Sesion
{
    private const INACTIVIDAD_SEGUNDOS = 1800;

    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        session_name('sesion_hotel');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();

        $ahora = time();
        $ultimaActividad = $_SESSION['ultima_actividad'] ?? $ahora;
        if (isset($_SESSION['usuario']) && $ahora - $ultimaActividad > self::INACTIVIDAD_SEGUNDOS) {
            self::cerrar();
            session_start();
        }
        $_SESSION['ultima_actividad'] = $ahora;
    }

    public static function iniciarUsuario(array $usuario): void
    {
        // Nuevo identificador al autenticarse: evita la fijación de sesión.
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
    }

    public static function usuarioActual(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function cerrar(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies') && !headers_sent()) {
            $parametros = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $parametros['path'],
                'secure' => $parametros['secure'],
                'httponly' => $parametros['httponly'],
                'samesite' => $parametros['samesite'],
            ]);
        }
        session_destroy();
    }
}
