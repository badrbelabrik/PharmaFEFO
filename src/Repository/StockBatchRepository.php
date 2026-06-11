<?php

namespace PharmaFEFO\Repository;

use PDO;
use DateTime;
use PharmaFEFO\Config\Database;
use PharmaFEFO\Entity\StockBatch;
use PharmaFEFO\Entity\Product;
use PharmaFEFO\Enum\BatchStatus;

class StockBatchRepository
{
    private PDO $connection;
    private ProductRepository $productRepository;

    public function __construct() {
        $this->connection = Database::getConnection();
        $this->productRepository = new ProductRepository();
    }

    /**
     * Find all stock batches with their criticality levels
     * Used by DashboardController::index()
     */
    public function findAllWithCriticality(): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.quantity > 0 
                AND sb.status != 'expired'
                ORDER BY sb.expiration_date ASC";

        $stmt = $this->connection->query($sql);
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Find batches that expire within a certain number of days
     * Used by DashboardController and StockController
     */
    public function findAlertBatches(int $daysThreshold): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.quantity > 0 
                AND sb.status != 'expired'
                AND DATEDIFF(sb.expiration_date, CURDATE()) <= :threshold
                AND DATEDIFF(sb.expiration_date, CURDATE()) >= 0
                ORDER BY sb.expiration_date ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':threshold' => $daysThreshold]);
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Find batches that are critical (expiring in less than 30 days)
     * Used by DashboardController
     */
    public function findCriticalBatches(): array {
        return $this->findAlertBatches(30);
    }

    /**
     * Find batches that are warning (expiring between 30-90 days)
     * Used by DashboardController
     */
    public function findWarningBatches(): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.quantity > 0 
                AND sb.status != 'expired'
                AND DATEDIFF(sb.expiration_date, CURDATE()) BETWEEN 31 AND 90
                ORDER BY sb.expiration_date ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Find healthy batches (expiring in more than 90 days)
     * Used by DashboardController
     */
    public function findHealthyBatches(): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.quantity > 0 
                AND sb.status != 'expired'
                AND DATEDIFF(sb.expiration_date, CURDATE()) > 90
                ORDER BY sb.expiration_date ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Find the earliest expiring batch for a product (FEFO rule)
     * Used by StockController for dispensing
     */
    public function findEarliestExpiringBatch(int $productId): ?StockBatch {
        $sql = "SELECT sb.* FROM stockbatches sb
                WHERE sb.id_product = :product_id 
                AND sb.quantity > 0 
                AND sb.status != 'expired'
                AND sb.expiration_date >= CURDATE()
                ORDER BY sb.expiration_date ASC 
                LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Find a batch by its ID
     * Used by StockController
     */
    public function findById(int $id): ?StockBatch {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;
        return $this->hydrate($data);
    }

    /**
     * Find all batches for a specific product
     * Used for product detail views
     */
    public function findByProductId(int $productId): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.id_product = :product_id 
                AND sb.quantity > 0
                ORDER BY sb.expiration_date ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Save a new stock batch
     * Used by StockController for receiving stock
     */
    public function save(StockBatch $batch): StockBatch {
        $sql = "INSERT INTO stockbatches (lot_number, quantity, purchase_price, status, expiration_date, created_at, id_product)
                VALUES (:lot_number, :quantity, :purchase_price, :status, :expiration_date, :created_at, :id_product)";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':lot_number' => $batch->getBatchNumber(),
            ':quantity' => $batch->getQuantity(),
            ':purchase_price' => $batch->getUnitPrice(),
            ':status' => $batch->getStatus()->value,
            ':expiration_date' => $batch->getExpirationDate()->format('Y-m-d'),
            ':created_at' => $batch->getReceivedDate()->format('Y-m-d H:i:s'),
            ':id_product' => $batch->getProduct()->getId()
        ]);

        $batch->setId((int)$this->connection->lastInsertId());

        // Record stock movement (IN)
        $this->recordStockMovement($batch->getId(), 'in', $batch->getQuantity());

        return $batch;
    }

    /**
     * Update batch quantity
     * Used by StockController when dispensing
     */
    public function updateQuantity(StockBatch $batch): void {
        // Auto-update status based on expiration
        $daysLeft = $batch->getDaysUntilExpiration();

        if ($daysLeft < 0) {
            $batch->setStatus(BatchStatus::EXPIRED);
        } elseif ($daysLeft <= 30) {
            $batch->setStatus(BatchStatus::CRITICAL);
        } elseif ($daysLeft <= 90) {
            $batch->setStatus(BatchStatus::WARNING);
        } else {
            $batch->setStatus(BatchStatus::ACTIVE);
        }

        $sql = "UPDATE stockbatches 
                SET quantity = :quantity, status = :status
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':quantity' => $batch->getQuantity(),
            ':status' => $batch->getStatus()->value,
            ':id' => $batch->getId()
        ]);
    }

    /**
     * Mark a batch as expired
     * Used by StockController
     */
    public function markAsExpired(StockBatch $batch): void {
        $batch->setStatus(BatchStatus::EXPIRED);

        $sql = "UPDATE stockbatches 
                SET status = :status
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':status' => BatchStatus::EXPIRED->value,
            ':id' => $batch->getId()
        ]);

        // Record stock movement (expired)
        $this->recordStockMovement($batch->getId(), 'out', $batch->getQuantity(), 'Expired');
    }

    /**
     * Record stock movement in stockmovements table
     */
    private function recordStockMovement(int $batchId, string $type, int $quantity, string $notes = ''): void {
        $sql = "INSERT INTO stockmovements (type, quantity, movement_date, id_batch, id_user)
                VALUES (:type, :quantity, :movement_date, :id_batch, :id_user)";

        $userId = $_SESSION['user_id'] ?? null;

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':type' => $type,
            ':quantity' => $quantity,
            ':movement_date' => (new DateTime())->format('Y-m-d H:i:s'),
            ':id_batch' => $batchId,
            ':id_user' => $userId
        ]);
    }

    /**
     * Dispense medication (decrement quantity and record movement)
     * Used by StockController for FEFO dispensing
     */
    public function dispense(StockBatch $batch, int $quantity): void {
        $oldQuantity = $batch->getQuantity();
        $batch->decrementQuantity($quantity);
        $this->updateQuantity($batch);

        // Record stock movement (OUT)
        $this->recordStockMovement($batch->getId(), 'out', $quantity, 'Dispensed');
    }

    /**
     * Delete a batch (soft delete or hard delete)
     * Used for admin operations
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM stockbatches WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get total value of expired stock for financial report
     * Used by ReportController
     */
    public function getExpiredStockValue(DateTime $startDate, DateTime $endDate): float {
        $sql = "SELECT SUM(quantity * purchase_price) as total_value
                FROM stockbatches
                WHERE status = 'expired'
                AND created_at BETWEEN :start_date AND :end_date";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate->format('Y-m-d H:i:s'),
            ':end_date' => $endDate->format('Y-m-d H:i:s')
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total_value'] ?? 0);
    }

    /**
     * Get total value of all stock (active batches)
     * Used by DashboardController
     */
    public function getTotalStockValue(): float {
        $sql = "SELECT SUM(quantity * purchase_price) as total_value
                FROM stockbatches
                WHERE status != 'expired' AND quantity > 0";

        $stmt = $this->connection->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total_value'] ?? 0);
    }

    /**
     * Get total number of active batches
     * Used by DashboardController
     */
    public function getTotalActiveBatches(): int {
        $sql = "SELECT COUNT(*) as total
                FROM stockbatches
                WHERE status != 'expired' AND quantity > 0";

        $stmt = $this->connection->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get low stock alerts (quantity below threshold)
     * Used for additional alerts
     */
    public function findLowStockBatches(int $threshold = 10): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.quantity <= :threshold 
                AND sb.quantity > 0
                AND sb.status != 'expired'
                ORDER BY sb.quantity ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':threshold' => $threshold]);
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Get summary statistics for dashboard
     * Used by DashboardController
     */
    public function getDashboardStats(): array {
        $sql = "SELECT 
                    COUNT(DISTINCT id_product) as total_products,
                    SUM(quantity * purchase_price) as total_value,
                    COUNT(*) as total_batches,
                    SUM(CASE WHEN DATEDIFF(expiration_date, CURDATE()) <= 30 AND quantity > 0 THEN 1 ELSE 0 END) as critical_count,
                    SUM(CASE WHEN DATEDIFF(expiration_date, CURDATE()) BETWEEN 31 AND 90 AND quantity > 0 THEN 1 ELSE 0 END) as warning_count,
                    SUM(CASE WHEN DATEDIFF(expiration_date, CURDATE()) > 90 AND quantity > 0 THEN 1 ELSE 0 END) as healthy_count
                FROM stockbatches
                WHERE status != 'expired'";

        $stmt = $this->connection->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Search batches by product name or lot number
     * Used for search functionality
     */
    public function search(string $keyword): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE (p.name LIKE :keyword 
                OR p.serial_number LIKE :keyword 
                OR sb.lot_number LIKE :keyword)
                AND sb.quantity > 0
                ORDER BY sb.expiration_date ASC";

        $stmt = $this->connection->prepare($sql);
        $searchTerm = '%' . $keyword . '%';
        $stmt->execute([':keyword' => $searchTerm]);
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Get batches that will expire in the next month
     * Used for notifications (US 2.2)
     */
    public function getExpiringNextMonth(): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.quantity > 0 
                AND sb.status != 'expired'
                AND DATEDIFF(sb.expiration_date, CURDATE()) BETWEEN 1 AND 30
                ORDER BY sb.expiration_date ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Get expired batches
     * Used for financial reports
     */
    public function getExpiredBatches(): array {
        $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                FROM stockbatches sb
                JOIN products p ON sb.id_product = p.id
                WHERE sb.status = 'expired'
                OR sb.expiration_date < CURDATE()
                ORDER BY sb.expiration_date DESC";

        $stmt = $this->connection->query($sql);
        $batches = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batches[] = $this->hydrate($data);
        }

        return $batches;
    }

    /**
     * Get stock movements for a batch
     * Used for audit trail
     */
    public function getStockMovements(int $batchId): array {
        $sql = "SELECT * FROM stockmovements 
                WHERE id_batch = :batch_id 
                ORDER BY movement_date DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':batch_id' => $batchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create notification for expiring batch
     * Used by alert system
     */
    public function createNotification(int $batchId, string $description): void {
        $sql = "INSERT INTO notifications (description, created_at, is_read, id_batch)
                VALUES (:description, :created_at, 0, :id_batch)";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':description' => $description,
            ':created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            ':id_batch' => $batchId
        ]);
    }

    /**
     * Get unread notifications
     * Used by dashboard
     */
    public function getUnreadNotifications(): array {
        $sql = "SELECT n.*, sb.lot_number, p.name as product_name
                FROM notifications n
                JOIN stockbatches sb ON n.id_batch = sb.id
                JOIN products p ON sb.id_product = p.id
                WHERE n.is_read = 0
                ORDER BY n.created_at DESC";

        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(int $notificationId): void {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $notificationId]);
    }

    /**
     * Hydrate database data into StockBatch entity
     */
    private function hydrate(array $data): StockBatch {
        $product = $this->productRepository->findById($data['id_product']);

        if (!$product) {
            throw new \Exception("Product not found for ID: " . $data['id_product']);
        }

        $batch = new StockBatch(

            $data['lot_number'],
            (int)$data['quantity'],
            (float)$data['purchase_price'],
            BatchStatus::from($data['status']),$product,
            new DateTime($data['expiration_date']),
            $data['created_at'],
            $product,
            $data['id']
        );
        $batch->setId((int)$data['id']);

        return $batch;
    }
}