<?php

namespace PharmaFEFO\Middleware;

use PharmaFEFO\Enum\UserRole;

class RoleMiddleware
{
    private static array $roleHierarchy = [
        'preparator' => 1,
        'pharmacist' => 2,
        'admin' => 3
    ];

    public static function hasRole(string $requiredRole): bool
    {
        if (!AuthMiddleware::isAuthenticated()) {
            return false;
        }

        $userRole = $_SESSION['user_role'] ?? null;

        if (!$userRole || !isset(self::$roleHierarchy[$userRole])) {
            return false;
        }

        $requiredLevel = self::$roleHierarchy[$requiredRole] ?? 0;
        $userLevel = self::$roleHierarchy[$userRole] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    public static function requireRole(string $requiredRole): void
    {
        if (!self::hasRole($requiredRole)) {
            header('Location: index.php?route=dashboard');
            exit();
        }
    }

    public static function getCurrentRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }
}