<?php

/**
 * Sesión de PHP con cookie segura y cierre por inactividad.
 * El usuario autenticado se guarda en $_SESSION['usuario'].
 */
class Sesion
{
    private const INACTIVIDAD_SEGUNDOS = 1800;
    // Aunque haya actividad constante, una sesión robada no sirve para siempre.
    private const DURACION_MAXIMA_SEGUNDOS = 28800;

    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        // Servidor compartido: las sesiones fuera de la carpeta temporal común a todos los proyectos.
        $carpetaSesiones = __DIR__ . '/../../storage/sesiones';
        // La crea Apache con permisos 0700: solo su usuario puede entrar. Si no puede, se usa la carpeta por defecto.
        if (!is_dir($carpetaSesiones)) {
            @mkdir($carpetaSesiones, 0700, true);
        }
        if (is_dir($carpetaSesiones) && is_writable($carpetaSesiones)) {
            ini_set('session.save_path', $carpetaSesiones);
            // Esta carpeta no la limpia el sistema operativo: PHP borra las sesiones vencidas.
            ini_set('session.gc_probability', '1');
            ini_set('session.gc_divisor', '100');
            // Solo en nuestra carpeta: en la compartida borraría sesiones de otros proyectos.
            ini_set('session.gc_maxlifetime', (string) self::INACTIVIDAD_SEGUNDOS);
        } else {
            error_log('Sesion: storage/sesiones no existe o Apache no puede escribir en ella; se usa la carpeta por defecto.');
        }
        session_name('sesion_hotel');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => self::rutaCookie(),
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();

        $ahora = time();
        $ultimaActividad = $_SESSION['ultima_actividad'] ?? $ahora;
        $inicio = $_SESSION['inicio_sesion'] ?? $ahora;
        if (isset($_SESSION['usuario'])
            && ($ahora - $ultimaActividad > self::INACTIVIDAD_SEGUNDOS || $ahora - $inicio > self::DURACION_MAXIMA_SEGUNDOS
                || !hash_equals($_SESSION['huella'] ?? '', self::huella()))) {
            self::cerrar();
            session_start();
        }
        $_SESSION['ultima_actividad'] = $ahora;
    }

    public static function iniciarUsuario(array $usuario): void
    {
        // Nuevo identificador al autenticarse: evita la fijación de sesión.
        session_regenerate_id(true);
        // Token CSRF nuevo: el que existía antes de autenticarse no sirve después.
        unset($_SESSION['csrf']);
        $_SESSION['inicio_sesion'] = time();
        $_SESSION['huella'] = self::huella();
        $_SESSION['usuario'] = $usuario;
    }

    /**
     * Ruta del proyecto en la URL ('/hotel/' en IP/hotel, '/' con php -S): la cookie
     * no se envía a los proyectos de otros equipos en el mismo servidor.
     */
    private static function rutaCookie(): string
    {
        // Se toma de la URL que usó el navegador (no de la carpeta en disco, que puede llamarse distinto).
        // Si no se puede deducir, '/': la cookie funciona aunque no quede aislada.
        $publica = realpath(__DIR__ . '/../../public');
        $script = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
        $url = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($publica === false || $script === false || !str_starts_with($script, $publica . DIRECTORY_SEPARATOR)) {
            return '/';
        }

        $relativa = str_replace(DIRECTORY_SEPARATOR, '/', substr($script, strlen($publica))); // '/admin/usuarios.php'
        $finales = ['/public' . $relativa, $relativa];
        if (str_ends_with($relativa, '/index.php')) {
            $finales[] = substr($relativa, 0, -strlen('index.php')); // '/hotel/' sirve index.php
        }
        foreach ($finales as $final) {
            if (str_ends_with($url, $final)) {
                return substr($url, 0, strlen($url) - strlen($final)) . '/';
            }
        }
        return '/';
    }

    /**
     * Huella del navegador: una cookie copiada a otro navegador no conserva la sesión.
     * No usa la IP para no cerrar sesiones de móviles que cambian de red.
     */
    private static function huella(): string
    {
        return hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    /** Refresca los datos del usuario ya autenticado (rol, permisos) sin reiniciar la sesión. */
    public static function actualizarUsuario(array $usuario): void
    {
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
