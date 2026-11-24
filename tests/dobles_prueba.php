<?php
/**
 * Dobles de prueba compartidos: repositorio y bitácora en memoria, sin base de datos.
 * Las consultas SQL reales de UsuarioRepositorio no se prueban aquí: requieren MariaDB.
 */

ob_start(); // Como en una página real: nada se envía antes de manejar la sesión.
$raiz = dirname(__DIR__) . '/app';
foreach (['nucleo/Conexion', 'nucleo/Sesion', 'nucleo/Csrf', 'nucleo/Bitacora', 'repositorios/UsuarioRepositorio', 'controladores/AuthController', 'controladores/UsuarioController'] as $archivo) {
    require "$raiz/$archivo.php";
}

// Como lo lanza MariaDB al violar un índice único: SQLSTATE 23000.
class ErrorCorreoDuplicado extends PDOException
{
    protected $code = '23000';
}

class RepositorioFalso extends UsuarioRepositorio
{
    public array $intentos = [];
    public array $hashesActualizados = [];

    public function __construct(private array $usuarios)
    {
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        return $this->usuarios[$correo] ?? null;
    }

    public function buscarPorId(int $idUsuario): ?array
    {
        foreach ($this->usuarios as $usuario) {
            if ((int) $usuario['id_usuario'] === $idUsuario) {
                return $usuario;
            }
        }
        return null;
    }

    /** Simula que un administrador cambia la cuenta (desactivarla, cambiarle el rol). */
    public function modificar(string $correo, array $cambios): void
    {
        $this->usuarios[$correo] = $cambios + $this->usuarios[$correo];
    }

    public function permisosDeRol(int $idRol): array
    {
        return $idRol === 2 ? ['consultar_reservaciones'] : ['gestionar_usuarios'];
    }

    public bool $simularCorreoDuplicado = false;

    public function crearCliente(string $nombre, string $apellidos, string $correo, ?string $telefono, string $contrasenaHash): int
    {
        if ($this->simularCorreoDuplicado) {
            throw new ErrorCorreoDuplicado('Duplicate entry');
        }
        $id = count($this->usuarios) + 100;
        $this->usuarios[$correo] = [
            'id_usuario' => $id, 'id_rol' => 2, 'rol' => 'cliente', 'nombre' => $nombre, 'apellidos' => $apellidos,
            'correo' => $correo, 'telefono' => $telefono, 'contrasena_hash' => $contrasenaHash,
            'esta_activo' => 1, 'esta_verificado' => 1,
        ];
        return $id;
    }

    public function actualizarContrasenaHash(int $idUsuario, string $hash): void
    {
        $this->hashesActualizados[$idUsuario] = $hash;
    }

    public function contarFallidosPorCorreo(string $correo, int $minutos): int
    {
        $fallidos = 0;
        foreach ($this->intentos as [$correoIntento, , $exitoso]) {
            if ($correoIntento === $correo) {
                $fallidos = $exitoso ? 0 : $fallidos + 1;
            }
        }
        return $fallidos;
    }

    public function contarFallidosPorIp(string $ip, int $minutos): int
    {
        return count(array_filter($this->intentos, fn ($intento) => $intento[1] === $ip && !$intento[2]));
    }

    public function registrarIntento(string $correo, string $ip, bool $fueExitoso): void
    {
        $this->intentos[] = [$correo, $ip, $fueExitoso];
    }
}

class BitacoraFalsa extends Bitacora
{
    public array $eventos = [];

    public function registrar(string $evento, ?int $idUsuario = null, ?string $detalle = null): void
    {
        $this->eventos[] = $evento;
    }

    // Todos los eventos de prueba vienen de la misma IP.
    public function contarPorIp(array $eventos, string $ip, int $minutos): int
    {
        return count(array_filter($this->eventos, fn ($evento) => in_array($evento, $eventos, true)));
    }
}

function usuarioPrueba(int $id, int $idRol, string $rol, string $correo, bool $activo = true, bool $verificado = true): array
{
    return [
        'id_usuario' => $id, 'id_rol' => $idRol, 'rol' => $rol, 'nombre' => 'Prueba', 'apellidos' => 'Uno',
        'correo' => $correo, 'contrasena_hash' => password_hash('Correcta#1', PASSWORD_BCRYPT, ['cost' => 4]),
        'esta_activo' => (int) $activo, 'esta_verificado' => (int) $verificado,
    ];
}

$fallas = 0;
function comprobar(string $descripcion, bool $condicion): void
{
    global $fallas;
    echo ($condicion ? '  ok   ' : '  FALLA ') . $descripcion . PHP_EOL;
    $fallas += $condicion ? 0 : 1;
}

function prepararSesionDePrueba(): void
{
    ini_set('session.save_path', sys_get_temp_dir());
    Sesion::iniciar();
}

function terminarPruebas(): never
{
    global $fallas;
    echo $fallas === 0 ? "\nTodas las comprobaciones pasaron.\n" : "\n$fallas comprobaciones fallaron.\n";
    exit($fallas === 0 ? 0 : 1);
}
