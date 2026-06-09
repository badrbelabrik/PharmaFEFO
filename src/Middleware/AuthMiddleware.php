<?php

namespace PharmaFEFO\Middleware;

class AuthMiddleware
{
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: index.php?route=login');
            exit();
        }
    }
}