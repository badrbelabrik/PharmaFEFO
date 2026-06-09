<?php

namespace Entity;

class LossReport
{
    private ?int $id;
    private string $reportDate;
    private float $totalLoss;
    private string $details;

    public function __construct(string $reportDate,float $totalLoss,string $details,?int $id = null){
        $this->reportDate = $reportDate;
        $this->totalLoss = $totalLoss;
        $this->details = $details;
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

    public function getReportDate(): string
    {
        return $this->reportDate;
    }

    public function setReportDate(string $reportDate): void
    {
        $this->reportDate = $reportDate;
    }

    public function getTotalLoss(): float
    {
        return $this->totalLoss;
    }

    public function setTotalLoss(float $totalLoss): void
    {
        $this->totalLoss = $totalLoss;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function setDetails(string $details): void
    {
        $this->details = $details;
    }
}