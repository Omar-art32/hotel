<?php

define('NOMBRE_HOTEL', 'Casa Cantera');

// Credenciales y datos del entorno: se leen de .env (raíz del proyecto, fuera de public/).
$rutaEntorno = __DIR__ . '/../../.env';
$entorno = is_readable($rutaEntorno) ? parse_ini_file($rutaEntorno, false, INI_SCANNER_RAW) : false;
if ($entorno === false) {
    http_response_code(500);
    exit('Falta el archivo .env o no se puede leer. Cópialo desde .env.example y completa los valores.');
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
