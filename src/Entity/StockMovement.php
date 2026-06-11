<?php

namespace PharmaFEFO\Entity;

class StockMovement
{
    private ?int $id;
    private string $type;
    private int $quantity;
    private string $movementDate;
    private StockBatch $stockBatch;
    private User $user;

    public function __construct(string $type,int $quantity,string $movementDate,StockBatch $stockBatch,User $user,?int $id = null){
        $this->type = $type;
        $this->quantity = $quantity;
        $this->movementDate = $movementDate;
        $this->stockBatch = $stockBatch;
        $this->user = $user;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getMovementDate(): string
    {
        return $this->movementDate;
    }

    public function setMovementDate(string $movementDate): void
    {
        $this->movementDate = $movementDate;
    }

    public function getStockBatch(): StockBatch
    {
        return $this->stockBatch;
    }

    public function setStockBatch(StockBatch $stockBatch): void
    {
        $this->stockBatch = $stockBatch;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}