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
        Sesion::iniciar();
        $usuario = Sesion::usuarioActual();

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
