<?php

/**
 * Registro de eventos del sistema en la tabla bitacora.
 */
class Bitacora
{
    public function __construct(private ?PDO $pdo = null)
    {
    }

    public function registrar(string $evento, ?int $idUsuario = null, ?string $detalle = null): void
    {
        $pdo = $this->pdo ?? Conexion::obtener();
        $consulta = $pdo->prepare('INSERT INTO bitacora (id_usuario, evento, detalle, ip) VALUES (?, ?, ?, ?)');
        $consulta->execute([$idUsuario, $evento, $detalle, self::ipCliente()]);
    }

    /** Eventos de esa IP en los últimos $minutos (por ejemplo, para limitar registros). */
    public function contarPorIp(array $eventos, string $ip, int $minutos): int
    {
        $pdo = $this->pdo ?? Conexion::obtener();
        $marcadores = implode(', ', array_fill(0, count($eventos), '?'));
        $consulta = $pdo->prepare(sprintf(
            'SELECT COUNT(*) FROM bitacora WHERE ip = ? AND evento IN (%s) AND fecha_creacion >= NOW() - INTERVAL %d MINUTE',
            $marcadores,
            $minutos
        ));
        $consulta->execute([$ip, ...$eventos]);
        return (int) $consulta->fetchColumn();
    }

    public static function ipCliente(): string
    {
        // REMOTE_ADDR es la única IP confiable: X-Forwarded-For lo puede falsificar el cliente.
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
