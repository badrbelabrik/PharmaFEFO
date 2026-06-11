<?php

namespace PharmaFEFO\Controller;

use PharmaFEFO\Repository\StockBatchRepository;
use PharmaFEFO\Repository\ProductRepository;

class DashboardController
{
    private StockBatchRepository $stockBatchRepo;
    private ProductRepository $productRepo;

    public function __construct() {
        $this->stockBatchRepo = new StockBatchRepository();
        $this->productRepo = new ProductRepository();
    }

    public function index(): void {
        $allBatches = $this->stockBatchRepo->findAllWithCriticality();

        $criticalBatches = [];
        $warningBatches = [];
        $healthyBatches = [];

        foreach ($allBatches as $batch) {
            $daysLeft = $batch->getDaysUntilExpiration();

            if ($daysLeft < 0) {
                continue;
            } elseif ($daysLeft <= 30) {
                $criticalBatches[] = $batch;
            } elseif ($daysLeft <= 90) {
                $warningBatches[] = $batch;
            } else {
                $healthyBatches[] = $batch;
            }
        }


        $totalProducts = count($this->productRepo->findAll());


        $totalStockValue = $this->calculateTotalStockValue($allBatches);


        $currentUser = $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'];
        $userRole = $_SESSION['user_role'] ?? 'preparator';


        require_once __DIR__ . '/../../templates/dashboard/index.php';
    }

    private function calculateTotalStockValue(array $batches): float {
        $total = 0;
        foreach ($batches as $batch) {
            $total += $batch->getTotalValue();
        }
        return $total;
    }
}