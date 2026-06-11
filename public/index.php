<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/autoloader.php';

use PharmaFEFO\Controller\AuthController;
use PharmaFEFO\Controller\DashboardController;
use PharmaFEFO\Controller\HomeController;
use PharmaFEFO\Controller\StockController;
use PharmaFEFO\Controller\ReportController;
use PharmaFEFO\Middleware\AuthMiddleware;
use PharmaFEFO\Middleware\RoleMiddleware;

$route = $_GET['route'] ?? 'home';

switch ($route) {
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;
    case 'login':
        $controller = new AuthController();
        $controller->login();
        break;

    case 'logout':
        AuthMiddleware::requireAuth();
        $controller = new AuthController();
        $controller->logout();
        break;


    case 'dashboard':
        AuthMiddleware::requireAuth();
        $controller = new DashboardController();
        $controller->index();
        break;


    case 'stock-receive':
        AuthMiddleware::requireAuth();
        RoleMiddleware::requireRole('preparator');
        $controller = new StockController();
        $controller->receive();
        break;

    case 'stock-dispatch':
        AuthMiddleware::requireAuth();
        RoleMiddleware::requireRole('preparator');
        $controller = new StockController();
        $controller->dispatch();
        break;

    case 'stock-alerts':
        AuthMiddleware::requireAuth();
        RoleMiddleware::requireRole('pharmacist');
        $controller = new StockController();
        $controller->alerts();
        break;

    case 'stock-expired':
        AuthMiddleware::requireAuth();
        RoleMiddleware::requireRole('pharmacist');
        $controller = new StockController();
        $controller->markAsExpired();
        break;

    case 'reports':
        AuthMiddleware::requireAuth();
        RoleMiddleware::requireRole('pharmacist');
        $controller = new ReportController();
        $controller->index();
        break;

    case 'report-financial':
        AuthMiddleware::requireAuth();
        RoleMiddleware::requireRole('admin');
        $controller = new ReportController();
        $controller->financial();
        break;

    default:
        http_response_code(404);
        require_once __DIR__ . '/../templates/errors/404.php';
        break;
}