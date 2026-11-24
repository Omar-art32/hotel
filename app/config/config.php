<?php

define('NOMBRE_HOTEL', 'Casa Cantera');

// Cabeceras de seguridad para todas las páginas (todas incluyen este archivo).
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
// same-origin: los tokens de la URL (restablecer contraseña) no viajan a Google Fonts en el Referer.
header('Referrer-Policy: same-origin');
// style-src 'unsafe-inline' solo por los atributos style="background-image: ..." de index.php.
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000');
}

// Credenciales y datos del entorno: se leen de .env (raíz del proyecto, fuera de public/).
$rutaEntorno = __DIR__ . '/../../.env';
$entorno = is_readable($rutaEntorno) ? parse_ini_file($rutaEntorno, false, INI_SCANNER_RAW) : false;
if ($entorno === false) {
    error_log('Falta el archivo .env o no se puede leer. Cópialo desde .env.example y completa los valores.');
    http_response_code(500);
    exit('El sistema no está disponible en este momento.');
}

define('DB_HOST', $entorno['DB_HOST'] ?? '127.0.0.1');
define('DB_PUERTO', $entorno['DB_PUERTO'] ?? '3306');
define('DB_NOMBRE', $entorno['DB_NOMBRE'] ?? '');
define('DB_USUARIO', $entorno['DB_USUARIO'] ?? '');
define('DB_CONTRASENA', $entorno['DB_CONTRASENA'] ?? '');
unset($rutaEntorno, $entorno);

// Carga automática de clases: Conexion, Sesion, AuthController, UsuarioRepositorio, etc.
spl_autoload_register(function (string $clase): void {
    foreach (['nucleo', 'controladores', 'repositorios'] as $carpeta) {
        $ruta = __DIR__ . "/../$carpeta/$clase.php";
        if (is_file($ruta)) {
            require $ruta;
            return;
        }
    }
});
