<?php
require __DIR__ . '/../app/config/config.php';

Sesion::iniciar();

// Solo por POST con token: un enlace o imagen de otro sitio no puede cerrar la sesión.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validar($_POST['csrf'] ?? null)) {
    header('Location: login.php');
    exit;
}

try {
    (new AuthController())->cerrarSesion();
} catch (PDOException $error) {
    // Sin base de datos no se registra en la bitácora, pero la sesión se cierra igual.
    error_log('Logout: ' . $error->getMessage());
    Sesion::cerrar();
}

header('Location: login.php?estado=sesion_cerrada');
exit;
