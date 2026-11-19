<?php

/**
 * Registro de cuentas de cliente.
 * Sin envío de correos: la cuenta queda habilitada al crearse. Después, la persona inicia sesión en login.php.
 */
class UsuarioController
{
    private UsuarioRepositorio $usuarios;
    private Bitacora $bitacora;

    public function __construct(?UsuarioRepositorio $usuarios = null, ?Bitacora $bitacora = null)
    {
        $this->usuarios = $usuarios ?? new UsuarioRepositorio();
        $this->bitacora = $bitacora ?? new Bitacora();
    }

    /**
     * @param array $datos Campos del formulario ($_POST).
     * @return array{estado: string, errores: array<string, string>}
     *         estado: exito, errores o correo_registrado. errores: mensaje por campo.
     */
    public function registrar(array $datos): array
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $apellidos = trim((string) ($datos['apellidos'] ?? ''));
        $correo = strtolower(trim((string) ($datos['correo'] ?? '')));
        $telefono = trim((string) ($datos['telefono'] ?? ''));
        $contrasena = (string) ($datos['contrasena'] ?? '');
        $confirmacion = (string) ($datos['confirmar_contrasena'] ?? '');

        $errores = [];
        if ($nombre === '') {
            $errores['nombre'] = 'Escribe tu nombre.';
        } elseif (!preg_match('/^.{1,60}$/u', $nombre)) {
            $errores['nombre'] = 'El nombre puede tener como máximo 60 caracteres.';
        }
        if ($apellidos === '') {
            $errores['apellidos'] = 'Escribe tus apellidos.';
        } elseif (!preg_match('/^.{1,80}$/u', $apellidos)) {
            $errores['apellidos'] = 'Los apellidos pueden tener como máximo 80 caracteres.';
        }
        if ($correo === '') {
            $errores['correo'] = 'Escribe tu correo electrónico.';
        } elseif (strlen($correo) > 254 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Escribe un correo con el formato nombre@dominio.com.';
        }
        if ($telefono !== '' && !preg_match('/^[0-9+()\s-]{7,20}$/', $telefono)) {
            $errores['telefono'] = 'Escribe un teléfono de 7 a 20 caracteres: números, espacios y + ( ) -.';
        }
        if (!preg_match('/^.{8,}$/us', $contrasena)) {
            $errores['contrasena'] = 'La contraseña necesita al menos 8 caracteres.';
        } elseif (strlen($contrasena) > 72) {
            // bcrypt ignora lo que pase de 72 bytes: se rechaza en lugar de recortarla sin avisar.
            $errores['contrasena'] = 'La contraseña puede tener como máximo 72 caracteres.';
        }
        if ($confirmacion === '') {
            $errores['confirmar_contrasena'] = 'Escribe de nuevo la contraseña.';
        } elseif ($confirmacion !== $contrasena) {
            $errores['confirmar_contrasena'] = 'Las contraseñas no coinciden.';
        }
        if (($datos['terminos'] ?? '') !== '1') {
            $errores['terminos'] = 'Acepta los términos para crear tu cuenta.';
        }
        if ($errores) {
            return ['estado' => 'errores', 'errores' => $errores];
        }

        $correoRegistrado = ['estado' => 'correo_registrado', 'errores' => ['correo' => 'Ya existe una cuenta con este correo.']];
        if ($this->usuarios->buscarPorCorreo($correo) !== null) {
            return $correoRegistrado;
        }

        try {
            $idUsuario = $this->usuarios->crearCliente(
                $nombre,
                $apellidos,
                $correo,
                $telefono === '' ? null : $telefono,
                password_hash($contrasena, PASSWORD_DEFAULT)
            );
        } catch (PDOException $error) {
            // 23000: el correo se registró entre la consulta y el INSERT (índice único).
            if ($error->getCode() === '23000') {
                return $correoRegistrado;
            }
            throw $error;
        }

        $this->bitacora->registrar('registro_usuario', $idUsuario);

        return ['estado' => 'exito', 'errores' => []];
    }
}
