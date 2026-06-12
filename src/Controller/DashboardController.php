<?php

namespace PharmaFEFO\Controller;

use PharmaFEFO\Repository\StockBatchRepository;
use PharmaFEFO\Repository\ProductRepository;
use PharmaFEFO\Service\StockBatchService;

class DashboardController
{
    private StockBatchRepository $stockBatchRepo;
    private ProductRepository $productRepo;

    public function __construct() {
        $this->stockBatchRepo = new StockBatchRepository();
        $this->productRepo = new ProductRepository();
    }

    public function index(): void {
        $currentFilter = $_GET['filter'] ?? 'all';

        $allBatches = $this->stockBatchRepo->findAllWithCriticality();

        $stats = StockBatchService::getBatchStatistics($allBatches);

        $displayBatches = match($currentFilter) {
            'critical' => $stats['critical'],
            'warning' => $stats['warning'],
            'healthy' => $stats['healthy'],
            default => $allBatches
        };

        $totalProducts = count($this->productRepo->findAll());

        $totalStockValue = StockBatchService::getTotalValueForBatches($allBatches);

        $currentUser = ($_SESSION['user_firstname'] ?? '') . ' ' . ($_SESSION['user_lastname'] ?? '');
        $userRole = $_SESSION['user_role'] ?? 'preparator';

        $unreadNotifications = $this->stockBatchRepo->getUnreadNotifications();
        $unreadNotificationsCount = count($unreadNotifications);

        require_once __DIR__ . '/../../templates/dashboard/dashboard.php';
    }
}