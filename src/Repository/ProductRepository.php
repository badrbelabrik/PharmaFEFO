<?php

namespace PharmaFEFO\Repository;

use PDO;
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
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;
        return $this->hydrate($data);
    }

    /**
     * Find a product by its serial number
     */
    public function findBySerialNumber(string $serialNumber): ?Product {
        $sql = "SELECT * FROM products WHERE serial_number = :serial_number";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':serial_number' => $serialNumber]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;
        return $this->hydrate($data);
    }

    /**
     * Find a product by its name
     */
    public function findByName(string $name): ?Product {
        $sql = "SELECT * FROM products WHERE name = :name";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':name' => $name]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;
        return $this->hydrate($data);
    }

    /**
     * Get all products
     * Used by DashboardController
     */
    public function findAll(): array {
        $sql = "SELECT * FROM products ORDER BY name ASC";
        $stmt = $this->connection->query($sql);
        $products = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->hydrate($data);
        }

        return $products;
    }

    /**
     * Search products by name or serial number
     */
    public function search(string $keyword): array {
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
    }

    /**
     * Save a new product
     */
    public function save(Product $product): Product {
        $sql = "INSERT INTO products (name, serial_number, description) 
                VALUES (:name, :serial_number, :description)";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':name' => $product->getName(),
            ':serial_number' => $product->getSerialNumber(),
            ':description' => $product->getDescription()
        ]);

        $product->setId((int)$this->connection->lastInsertId());
        return $product;
    }

    /**
     * Update a product
     */
    public function update(Product $product): bool {
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
    }

    /**
     * Delete a product
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get total number of products
     */
    public function getTotalCount(): int {
        $sql = "SELECT COUNT(*) as total FROM products";
        $stmt = $this->connection->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Hydrate database data into Product entity
     */
    private function hydrate(array $data): Product {
        $product = new Product(
            $data['serial_number'],  // code/serial_number
            $data['name'], // Default price (you may want to add this to your products table)
            $data['description'] ?? null,
            $data['id']
        );

        return $product;
    }
}