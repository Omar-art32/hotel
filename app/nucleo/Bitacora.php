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

    public static function ipCliente(): string
    {
        // REMOTE_ADDR es la única IP confiable: X-Forwarded-For lo puede falsificar el cliente.
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
