<?php

/**
 * Protege páginas: exige sesión iniciada y un permiso del rol.
 */
class Autorizacion
{
    /**
     * Devuelve el usuario actual o detiene la página.
     * $rutaBase: '' para páginas en public/, '../' para páginas en subcarpetas.
     */
    public static function requerirPermiso(string $permiso, string $rutaBase = ''): array
    {
        // Páginas privadas fuera de la caché: "atrás" no debe mostrarlas sin pasar por el servidor.
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        Sesion::iniciar();
        try {
            $usuario = (new AuthController())->revalidarSesion();
        } catch (PDOException $error) {
            error_log('Autorizacion: ' . $error->getMessage());
            http_response_code(503);
            exit('El sistema no está disponible en este momento. Inténtalo de nuevo en unos minutos.');
        }

        if ($usuario === null) {
            header('Location: ' . $rutaBase . 'login.php?estado=acceso_requerido');
            exit;
        }

        if (!in_array($permiso, $usuario['permisos'], true)) {
            http_response_code(403);
            exit('No tienes permiso para ver esta página.');
        }

        return $usuario;
    }
}
