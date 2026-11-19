<?php

/**
 * Consultas de usuarios, permisos e intentos de inicio de sesión.
 * Todas las consultas son preparadas.
 */
class UsuarioRepositorio
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexion::obtener();
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT u.id_usuario, u.id_rol, u.nombre, u.apellidos, u.correo, u.contrasena_hash,
                    u.esta_activo, u.esta_verificado, r.nombre AS rol
             FROM usuarios u
             INNER JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.correo = ?
             LIMIT 1'
        );
        $consulta->execute([$correo]);
        return $consulta->fetch() ?: null;
    }

    /** @return string[] Códigos de permiso del rol. */
    public function permisosDeRol(int $idRol): array
    {
        $consulta = $this->pdo->prepare(
            'SELECT p.codigo
             FROM roles_permisos rp
             INNER JOIN permisos p ON p.id_permiso = rp.id_permiso
             WHERE rp.id_rol = ?'
        );
        $consulta->execute([$idRol]);
        return $consulta->fetchAll(PDO::FETCH_COLUMN);
    }

    public function actualizarContrasenaHash(int $idUsuario, string $hash): void
    {
        $consulta = $this->pdo->prepare('UPDATE usuarios SET contrasena_hash = ? WHERE id_usuario = ?');
        $consulta->execute([$hash, $idUsuario]);
    }

    /** Fallos del correo en la ventana de tiempo, contados desde su último acceso correcto. */
    public function contarFallidosPorCorreo(string $correo, int $minutos): int
    {
        $consulta = $this->pdo->prepare(sprintf(
            'SELECT COUNT(*)
             FROM intentos_login
             WHERE correo = ?
               AND fue_exitoso = FALSE
               AND fecha_creacion >= NOW() - INTERVAL %d MINUTE
               AND fecha_creacion > COALESCE(
                   (SELECT MAX(i.fecha_creacion) FROM intentos_login i WHERE i.correo = ? AND i.fue_exitoso = TRUE),
                   \'1000-01-01\'
               )',
            $minutos
        ));
        $consulta->execute([$correo, $correo]);
        return (int) $consulta->fetchColumn();
    }

    public function contarFallidosPorIp(string $ip, int $minutos): int
    {
        $consulta = $this->pdo->prepare(sprintf(
            'SELECT COUNT(*)
             FROM intentos_login
             WHERE ip = ?
               AND fue_exitoso = FALSE
               AND fecha_creacion >= NOW() - INTERVAL %d MINUTE',
            $minutos
        ));
        $consulta->execute([$ip]);
        return (int) $consulta->fetchColumn();
    }

    public function registrarIntento(string $correo, string $ip, bool $fueExitoso): void
    {
        $consulta = $this->pdo->prepare('INSERT INTO intentos_login (correo, ip, fue_exitoso) VALUES (?, ?, ?)');
        $consulta->execute([substr($correo, 0, 254), $ip, (int) $fueExitoso]);
    }
}
