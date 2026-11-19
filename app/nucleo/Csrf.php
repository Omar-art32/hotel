<?php

/**
 * Token contra falsificación de peticiones (CSRF).
 * Cada formulario POST incluye Csrf::campo() y el servidor llama a Csrf::validar().
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function campo(): string
    {
        return '<input type="hidden" name="csrf" value="' . self::token() . '">';
    }

    public static function validar(?string $token): bool
    {
        return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }
}
