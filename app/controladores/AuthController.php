<?php

/**
 * Inicio y cierre de sesión.
 * iniciarSesion() devuelve un estado que la página traduce a un mensaje:
 * exito, campos_vacios, credenciales, bloqueada, sin_verificar o bloqueo_temporal.
 */
class AuthController
{
    public const MAX_FALLIDOS_CORREO = 5;
    public const MAX_FALLIDOS_IP = 20;
    public const VENTANA_MINUTOS = 15;

    private const DESTINOS = [
        'cliente' => 'mis_reservaciones.php',
        'recepcionista' => 'recepcion/panel.php',
        'administrador' => 'admin/habitaciones.php',
        'superadministrador' => 'admin/usuarios.php',
    ];

    // Hash de una contraseña aleatoria. Se verifica cuando el correo no existe,
    // para que la respuesta tarde lo mismo y no revele qué correos están registrados.
    private const HASH_FALSO = '$2y$12$e6aAz7DWqt0XwVhbhE0LmemCJeHxy26BLX0Jp2YQFOqPS/2mHtamq';

    private UsuarioRepositorio $usuarios;
    private Bitacora $bitacora;

    public function __construct(?UsuarioRepositorio $usuarios = null, ?Bitacora $bitacora = null)
    {
        $this->usuarios = $usuarios ?? new UsuarioRepositorio();
        $this->bitacora = $bitacora ?? new Bitacora();
    }

    public function iniciarSesion(string $correo, string $contrasena, string $ip): string
    {
        $correo = strtolower(trim($correo));
        if ($correo === '' || $contrasena === '') {
            return 'campos_vacios';
        }

        if ($this->usuarios->contarFallidosPorCorreo($correo, self::VENTANA_MINUTOS) >= self::MAX_FALLIDOS_CORREO
            || $this->usuarios->contarFallidosPorIp($ip, self::VENTANA_MINUTOS) >= self::MAX_FALLIDOS_IP) {
            return 'bloqueo_temporal';
        }

        $usuario = filter_var($correo, FILTER_VALIDATE_EMAIL) ? $this->usuarios->buscarPorCorreo($correo) : null;
        $contrasenaCorrecta = password_verify($contrasena, $usuario['contrasena_hash'] ?? self::HASH_FALSO);

        if ($usuario === null || !$contrasenaCorrecta) {
            $this->usuarios->registrarIntento($correo, $ip, false);
            return 'credenciales';
        }

        // Estos estados solo se muestran con la contraseña correcta, así no revelan qué cuentas existen.
        if (!$usuario['esta_activo']) {
            $this->bitacora->registrar('acceso_cuenta_inactiva', (int) $usuario['id_usuario']);
            return 'bloqueada';
        }
        if (!$usuario['esta_verificado']) {
            return 'sin_verificar';
        }

        $idUsuario = (int) $usuario['id_usuario'];
        if (password_needs_rehash($usuario['contrasena_hash'], PASSWORD_DEFAULT)) {
            $this->usuarios->actualizarContrasenaHash($idUsuario, password_hash($contrasena, PASSWORD_DEFAULT));
        }

        $this->usuarios->registrarIntento($correo, $ip, true);
        $this->abrirSesion($usuario);

        return 'exito';
    }

    /** Guarda en la sesión al usuario (fila de buscarPorCorreo) con sus permisos y su destino. */
    private function abrirSesion(array $usuario): void
    {
        $idUsuario = (int) $usuario['id_usuario'];
        Sesion::iniciarUsuario([
            'id_usuario' => $idUsuario,
            'nombre' => $usuario['nombre'],
            'apellidos' => $usuario['apellidos'],
            'correo' => $usuario['correo'],
            'rol' => $usuario['rol'],
            'permisos' => $this->usuarios->permisosDeRol((int) $usuario['id_rol']),
            'destino' => self::DESTINOS[$usuario['rol']] ?? 'index.php',
        ]);
        $this->bitacora->registrar('inicio_sesion', $idUsuario);
    }

    public function cerrarSesion(): void
    {
        $usuario = Sesion::usuarioActual();
        try {
            if ($usuario !== null) {
                $this->bitacora->registrar('cierre_sesion', $usuario['id_usuario']);
            }
        } finally {
            Sesion::cerrar();
        }
    }
}
