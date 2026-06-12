<?php

namespace PharmaFEFO\Repository;

use PDO;
use PDOException;
use DateTime;
use PharmaFEFO\Config\Database;
use PharmaFEFO\Entity\StockBatch;
use PharmaFEFO\Entity\Product;
use PharmaFEFO\Enum\BatchStatus;
use PharmaFEFO\Service\StockBatchService;

class StockBatchRepository
{
    private PDO $connection;
    private ProductRepository $productRepository;

    public function __construct() {
        $this->connection = Database::getConnection();
        $this->productRepository = new ProductRepository();
    }

    public function findAllWithCriticality(): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in findAllWithCriticality: " . $e->getMessage());
            return [];
        }
    }

    public function findAlertBatches(int $daysThreshold): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in findAlertBatches: " . $e->getMessage());
            return [];
        }
    }

    public function findCriticalBatches(): array {
        try {
            return $this->findAlertBatches(30);
        } catch (PDOException $e) {
            error_log("Error in findCriticalBatches: " . $e->getMessage());
            return [];
        }
    }

    public function findWarningBatches(): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in findWarningBatches: " . $e->getMessage());
            return [];
        }
    }

    public function findHealthyBatches(): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in findHealthyBatches: " . $e->getMessage());
            return [];
        }
    }

    public function findEarliestExpiringBatch(int $productId): ?StockBatch {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in findEarliestExpiringBatch: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find a batch by its ID
     * Used by StockController
     */
    public function findById(int $id): ?StockBatch {
        try {
            $sql = "SELECT sb.*, p.name as product_name, p.serial_number 
                    FROM stockbatches sb
                    JOIN products p ON sb.id_product = p.id
                    WHERE sb.id = :id";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) return null;
            return $this->hydrate($data);
        } catch (PDOException $e) {
            error_log("Error in findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find all batches for a specific product
     * Used for product detail views
     */
    public function findByProductId(int $productId): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in findByProductId: " . $e->getMessage());
            return [];
        }
    }


    public function save(StockBatch $batch): ?StockBatch {
        try {
            $sql = "INSERT INTO stockbatches (lot_number, quantity, purchase_price, status, expiration_date, created_at, id_product)
                    VALUES (:lot_number, :quantity, :purchase_price, :status, :expiration_date, :created_at, :id_product)";

            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([
                ':lot_number' => $batch->getLotNumber(),
                ':quantity' => $batch->getQuantity(),
                ':purchase_price' => $batch->getPurchasePrice(),
                ':status' => $batch->getStatus()->value,
                ':expiration_date' => $batch->getExpirationDate()->format('Y-m-d'),
                ':created_at' => $batch->getCreatedAt(),
                ':id_product' => $batch->getProduct()->getId()
            ]);

            if ($result) {
                $batch->setId((int)$this->connection->lastInsertId());
                // Record stock movement (IN)
                $this->recordStockMovement($batch->getId(), 'in', $batch->getQuantity());
                return $batch;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error in save: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update batch quantity
     * Used by StockController when dispensing
     */
    public function updateQuantity(StockBatch $batch): bool {
        try {
            // Calculate days until expiration using service
            $daysLeft = StockBatchService::getDaysUntilExpiration($batch);

            // Auto-update status based on expiration
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
            return $stmt->execute([
                ':quantity' => $batch->getQuantity(),
                ':status' => $batch->getStatus()->value,
                ':id' => $batch->getId()
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateQuantity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark a batch as expired
     * Used by StockController
     */
    public function markAsExpired(StockBatch $batch): bool {
        try {
            $batch->setStatus(BatchStatus::EXPIRED);

            $sql = "UPDATE stockbatches 
                    SET status = :status
                    WHERE id = :id";

            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([
                ':status' => BatchStatus::EXPIRED->value,
                ':id' => $batch->getId()
            ]);

            if ($result) {
                // Record stock movement (expired)
                $this->recordStockMovement($batch->getId(), 'out', $batch->getQuantity(), 'Expired');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error in markAsExpired: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record stock movement in stockmovements table
     */
    private function recordStockMovement(int $batchId, string $type, int $quantity, string $notes = ''): bool {
        try {
            $sql = "INSERT INTO stockmovements (type, quantity, movement_date, id_batch, id_user)
                    VALUES (:type, :quantity, :movement_date, :id_batch, :id_user)";

            $userId = $_SESSION['user_id'] ?? null;

            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':type' => $type,
                ':quantity' => $quantity,
                ':movement_date' => (new DateTime())->format('Y-m-d H:i:s'),
                ':id_batch' => $batchId,
                ':id_user' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Error in recordStockMovement: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dispense medication (decrement quantity and record movement)
     * Used by StockController for FEFO dispensing
     */
    public function dispense(StockBatch $batch, int $quantity): bool {
        try {
            $oldQuantity = $batch->getQuantity();
            $newQuantity = $oldQuantity - $quantity;

            if ($newQuantity < 0) {
                error_log("Error in dispense: Insufficient stock. Available: {$oldQuantity}, Requested: {$quantity}");
                return false;
            }

            $batch->setQuantity($newQuantity);
            $this->updateQuantity($batch);

            // Record stock movement (OUT)
            return $this->recordStockMovement($batch->getId(), 'out', $quantity, 'Dispensed');
        } catch (PDOException $e) {
            error_log("Error in dispense: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a batch (soft delete or hard delete)
     * Used for admin operations
     */
    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM stockbatches WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total value of expired stock for financial report
     * Used by ReportController
     */
    public function getExpiredStockValue(DateTime $startDate, DateTime $endDate): float {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in getExpiredStockValue: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get total value of all stock (active batches)
     * Used by DashboardController
     */
    public function getTotalStockValue(): float {
        try {
            $sql = "SELECT SUM(quantity * purchase_price) as total_value
                    FROM stockbatches
                    WHERE status != 'expired' AND quantity > 0";

            $stmt = $this->connection->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['total_value'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error in getTotalStockValue: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get total number of active batches
     * Used by DashboardController
     */
    public function getTotalActiveBatches(): int {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM stockbatches
                    WHERE status != 'expired' AND quantity > 0";

            $stmt = $this->connection->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error in getTotalActiveBatches: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get low stock alerts (quantity below threshold)
     * Used for additional alerts
     */
    public function findLowStockBatches(int $threshold = 10): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in findLowStockBatches: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get summary statistics for dashboard
     * Used by DashboardController
     */
    public function getDashboardStats(): array {
        try {
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
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: [
                'total_products' => 0,
                'total_value' => 0,
                'total_batches' => 0,
                'critical_count' => 0,
                'warning_count' => 0,
                'healthy_count' => 0
            ];
        } catch (PDOException $e) {
            error_log("Error in getDashboardStats: " . $e->getMessage());
            return [
                'total_products' => 0,
                'total_value' => 0,
                'total_batches' => 0,
                'critical_count' => 0,
                'warning_count' => 0,
                'healthy_count' => 0
            ];
        }
    }

    /**
     * Search batches by product name or lot number
     * Used for search functionality
     */
    public function search(string $keyword): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in search: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get batches that will expire in the next month
     * Used for notifications (US 2.2)
     */
    public function getExpiringNextMonth(): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in getExpiringNextMonth: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get expired batches
     * Used for financial reports
     */
    public function getExpiredBatches(): array {
        try {
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
        } catch (PDOException $e) {
            error_log("Error in getExpiredBatches: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get stock movements for a batch
     * Used for audit trail
     */
    public function getStockMovements(int $batchId): array {
        try {
            $sql = "SELECT * FROM stockmovements 
                    WHERE id_batch = :batch_id 
                    ORDER BY movement_date DESC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':batch_id' => $batchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStockMovements: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create notification for expiring batch
     * Used by alert system
     */
    public function createNotification(int $batchId, string $description): bool {
        try {
            $sql = "INSERT INTO notifications (description, created_at, is_read, id_batch)
                    VALUES (:description, :created_at, 0, :id_batch)";

            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':description' => $description,
                ':created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                ':id_batch' => $batchId
            ]);
        } catch (PDOException $e) {
            error_log("Error in createNotification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notifications
     * Used by dashboard
     */
    public function getUnreadNotifications(): array {
        try {
            $sql = "SELECT n.*, sb.lot_number, p.name as product_name
                    FROM notifications n
                    JOIN stockbatches sb ON n.id_batch = sb.id
                    JOIN products p ON sb.id_product = p.id
                    WHERE n.is_read = 0
                    ORDER BY n.created_at DESC";

            $stmt = $this->connection->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getUnreadNotifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(int $notificationId): bool {
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $notificationId]);
        } catch (PDOException $e) {
            error_log("Error in markNotificationAsRead: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hydrate database data into StockBatch entity
     */
    private function hydrate(array $data): StockBatch {
        try {
            $product = $this->productRepository->findById($data['id_product']);

            if (!$product) {
                throw new \Exception("Product not found for ID: " . $data['id_product']);
            }

            $batch = new StockBatch(
                $data['lot_number'],
                (int)$data['quantity'],
                (float)$data['purchase_price'],
                BatchStatus::from($data['status']),
                new DateTime($data['expiration_date']),
                $data['created_at'],
                $product,
                isset($data['id']) ? (int)$data['id'] : null
            );

            return $batch;
        } catch (\Exception $e) {
            error_log("Error in hydrate: " . $e->getMessage());
            throw new \Exception("Failed to hydrate StockBatch: " . $e->getMessage());
        }
    }
}