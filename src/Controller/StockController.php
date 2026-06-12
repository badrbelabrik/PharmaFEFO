<?php

declare(strict_types=1);

namespace PharmaFEFO\Controller;

use DateTime;
use PharmaFEFO\Repository\StockBatchRepository;
use PharmaFEFO\Repository\ProductRepository;
use PharmaFEFO\Entity\StockBatch;
use PharmaFEFO\Enum\BatchStatus;
use PharmaFEFO\Service\StockBatchService;

class StockController
{
    private StockBatchRepository $stockBatchRepo;
    private ProductRepository $productRepo;

    public function __construct() {
        $this->stockBatchRepo = new StockBatchRepository();
        $this->productRepo = new ProductRepository();
    }

    public function receive(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleReceivePost();
        } else {
            $this->showReceiveForm();
        }
    }

    private function showReceiveForm(): void {
        $products = $this->productRepo->findAll();
        $currentUser = $_SESSION['user_firstname'] . ' ' . ($_SESSION['user_lastname'] ?? '');
        $userRole = $_SESSION['user_role'] ?? 'preparator';

        require_once __DIR__ . '/../../templates/dashboard/receive.php';
    }

    private function handleReceivePost(): void {
        $errors = [];

        // Get and validate form data
        $productId = (int)($_POST['product_id'] ?? 0);
        $lotNumber = trim($_POST['lot_number'] ?? '');
        $expirationDateString = $_POST['expiration_date'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 0);
        $purchasePrice = (float)($_POST['purchase_price'] ?? 0);

        // Validate product
        if ($productId <= 0) {
            $errors[] = "Please select a valid medication.";
        }

        // Validate lot number
        if (empty($lotNumber)) {
            $errors[] = "Lot number is required.";
        }

        // Validate expiration date
        if (empty($expirationDateString)) {
            $errors[] = "Expiration date is required.";
        } else {
            $expirationDate = new DateTime($expirationDateString);
            $today = new DateTime();
            $today->setTime(0, 0, 0);

            if ($expirationDate < $today) {
                $errors[] = "Expiration date cannot be in the past.";
            }
        }

        // Validate quantity
        if ($quantity <= 0) {
            $errors[] = "Quantity must be greater than 0.";
        }

        // Validate purchase price
        if ($purchasePrice < 0) {
            $errors[] = "Purchase price cannot be negative.";
        }

        // If no errors, save the batch
        if (empty($errors)) {
            try {
                $product = $this->productRepo->findById($productId);

                if (!$product) {
                    $errors[] = "Product not found.";
                } else {
                    $batch = new StockBatch(
                        $lotNumber,
                        $quantity,
                        $purchasePrice,
                        BatchStatus::OK,
                        $expirationDate,
                        (new DateTime())->format('Y-m-d H:i:s'),
                        $product
                    );

                    $savedBatch = $this->stockBatchRepo->save($batch);

                    if ($savedBatch) {
                        // Create notification if needed
                        $daysUntilExpiry = StockBatchService::getDaysUntilExpiration($batch);
                        if ($daysUntilExpiry <= 90) {
                            $this->stockBatchRepo->createNotification(
                                $savedBatch->getId(),
                                "Batch {$lotNumber} for {$product->getName()} expires in {$daysUntilExpiry} days."
                            );
                        }

                        $successMessage = "Batch {$lotNumber} successfully added to stock!";
                        $products = $this->productRepo->findAll();
                        $currentUser = $_SESSION['user_firstname'] . ' ' . ($_SESSION['user_lastname'] ?? '');
                        $userRole = $_SESSION['user_role'] ?? 'preparator';
                        require_once __DIR__ . '/../../templates/dashboard/receive.php';
                        return;
                    } else {
                        $errors[] = "Failed to save batch. Please try again.";
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "An error occurred: " . $e->getMessage();
            }
        }

        // If we get here, there were errors
        $products = $this->productRepo->findAll();
        $currentUser = $_SESSION['user_firstname'] . ' ' . ($_SESSION['user_lastname'] ?? '');
        $userRole = $_SESSION['user_role'] ?? 'preparator';
        $errorMessage = "Please fix the errors below.";
        require_once __DIR__ . '/../../templates/dashboard/receive.php';
    }

    public function dispatch(): void {
        // Will be implemented later
        echo "Dispatch method - FEFO dispensing coming soon";
    }

    public function alerts(): void {
        // Will be implemented later
        echo "Alerts method - Coming soon";
    }

    public function markAsExpired(): void {
        // Will be implemented later
        echo "Mark as expired - Coming soon";
    }
}