<?php

namespace Entity;

class Product
{
    private ?int $id;
    private string $name;
    private string $serialNumber;
    private string $description;

    public function __construct(string $name,string $serialNumber,string $description,?int $id = null){
        $this->name = $name;
        $this->serialNumber = $serialNumber;
        $this->description = $description;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): void
    {
        $this->serialNumber = $serialNumber;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


}