<?php

namespace PharmaFEFO\Repository;

use PDO;
use PDOException;
use PharmaFEFO\Config\Database;
use PharmaFEFO\Entity\Product;

class ProductRepository
{
    private PDO $connection;

    public function __construct() {
        $this->connection = Database::getConnection();
    }

    /**
     * Find a product by its ID
     */
    public function findById(int $id): ?Product {
        try {
            $sql = "SELECT * FROM products WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }
            return $this->hydrate($data);
        } catch (PDOException $e) {
            error_log("Error in findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find a product by its serial number
     */
    public function findBySerialNumber(string $serialNumber): ?Product {
        try {
            $sql = "SELECT * FROM products WHERE serial_number = :serial_number";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':serial_number' => $serialNumber]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }
            return $this->hydrate($data);
        } catch (PDOException $e) {
            error_log("Error in findBySerialNumber: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find a product by its name
     */
    public function findByName(string $name): ?Product {
        try {
            $sql = "SELECT * FROM products WHERE name = :name";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':name' => $name]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }
            return $this->hydrate($data);
        } catch (PDOException $e) {
            error_log("Error in findByName: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all products
     * Used by DashboardController
     */
    public function findAll(): array {
        try {
            $sql = "SELECT * FROM products ORDER BY name ASC";
            $stmt = $this->connection->query($sql);
            $products = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $this->hydrate($data);
            }

            return $products;
        } catch (PDOException $e) {
            error_log("Error in findAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search products by name or serial number
     */
    public function search(string $keyword): array {
        try {
            $sql = "SELECT * FROM products 
                    WHERE name LIKE :keyword 
                    OR serial_number LIKE :keyword 
                    ORDER BY name ASC";

            $stmt = $this->connection->prepare($sql);
            $searchTerm = '%' . $keyword . '%';
            $stmt->execute([':keyword' => $searchTerm]);
            $products = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $this->hydrate($data);
            }

            return $products;
        } catch (PDOException $e) {
            error_log("Error in search: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save a new product
     */
    public function save(Product $product): ?Product {
        try {
            $sql = "INSERT INTO products (name, serial_number, description) 
                    VALUES (:name, :serial_number, :description)";

            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([
                ':name' => $product->getName(),
                ':serial_number' => $product->getSerialNumber(),
                ':description' => $product->getDescription()
            ]);

            if ($result) {
                $product->setId((int)$this->connection->lastInsertId());
                return $product;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error in save: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a product
     */
    public function update(Product $product): bool {
        try {
            $sql = "UPDATE products 
                    SET name = :name, serial_number = :serial_number, description = :description 
                    WHERE id = :id";

            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':name' => $product->getName(),
                ':serial_number' => $product->getSerialNumber(),
                ':description' => $product->getDescription(),
                ':id' => $product->getId()
            ]);
        } catch (PDOException $e) {
            error_log("Error in update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a product
     */
    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM products WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total number of products
     */
    public function getTotalCount(): int {
        try {
            $sql = "SELECT COUNT(*) as total FROM products";
            $stmt = $this->connection->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error in getTotalCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if product exists by serial number
     */
    public function existsBySerialNumber(string $serialNumber): bool {
        try {
            $sql = "SELECT COUNT(*) as count FROM products WHERE serial_number = :serial_number";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':serial_number' => $serialNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error in existsBySerialNumber: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get products with low stock (based on batch quantities)
     * This requires a join with stockbatches
     */
    public function getProductsWithLowStock(int $threshold = 10): array {
        try {
            $sql = "SELECT p.*, SUM(sb.quantity) as total_quantity
                    FROM products p
                    LEFT JOIN stockbatches sb ON p.id = sb.id_product
                    WHERE sb.status != 'expired' OR sb.status IS NULL
                    GROUP BY p.id
                    HAVING total_quantity <= :threshold OR total_quantity IS NULL
                    ORDER BY total_quantity ASC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':threshold' => $threshold]);
            $products = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $this->hydrate($data);
            }

            return $products;
        } catch (PDOException $e) {
            error_log("Error in getProductsWithLowStock: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get products by category (if you add category column)
     */
    public function findByCategory(string $category): array {
        try {
            // Note: Add category column to products table if needed
            $sql = "SELECT * FROM products WHERE category = :category ORDER BY name ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':category' => $category]);
            $products = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $this->hydrate($data);
            }

            return $products;
        } catch (PDOException $e) {
            error_log("Error in findByCategory: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent products (last 30 days)
     */
    public function getRecentProducts(int $limit = 10): array {
        try {
            $sql = "SELECT * FROM products 
                    ORDER BY id DESC 
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $this->hydrate($data);
            }

            return $products;
        } catch (PDOException $e) {
            error_log("Error in getRecentProducts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Hydrate database data into Product entity
     */
    private function hydrate(array $data): Product {
        $product = new Product(
            $data['name'],
            $data['serial_number'],
            $data['description'] ?? null,
            (int)$data['id']
        );
        return $product;
    }
}