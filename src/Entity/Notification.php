<?php

namespace Entity;

class Notification
{
    private ?int $id;
    private string $description;
    private string $createdAt;
    private bool $isRead;
    private StockBatch $stockBatch;

    public function __construct(string $description,string $createdAt,bool $isRead,StockBatch $stockBatch,?int $id = null){
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->isRead = $isRead;
        $this->stockBatch = $stockBatch;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): void
    {
        $this->isRead = $isRead;
    }

    public function getStockBatch(): StockBatch
    {
        return $this->stockBatch;
    }

    public function setStockBatch(StockBatch $stockBatch): void
    {
        $this->stockBatch = $stockBatch;
    }


}