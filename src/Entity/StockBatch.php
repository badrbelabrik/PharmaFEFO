<?php

namespace Entity;

use DateTime;

class StockBatch
{
    private ?int $id;
    private string $lotNumber;
    private int $quantity;
    private float $purchasePrice;
    private string $status;
    private string $expirationDate;
    private string $createdAt;
    private Product $product;

    public function __construct(string $lotNumber,int $quantity,float $purchasePrice,string $status,string $expirationDate,string $createdAt,Product $product,?int $id = null){
        $this->lotNumber = $lotNumber;
        $this->quantity = $quantity;
        $this->purchasePrice = $purchasePrice;
        $this->status = $status;
        $this->expirationDate = $expirationDate;
        $this->createdAt = $createdAt;
        $this->product = $product;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getLotNumber(): string
    {
        return $this->lotNumber;
    }

    public function setLotNumber(string $lotNumber): void
    {
        $this->lotNumber = $lotNumber;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getPurchasePrice(): float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(float $purchasePrice): void
    {
        $this->purchasePrice = $purchasePrice;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getExpirationDate(): string
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(string $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

}