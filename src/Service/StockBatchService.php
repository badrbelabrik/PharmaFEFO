<?php

namespace PharmaFEFO\Service;

use DateTime;
use PharmaFEFO\Entity\StockBatch;
use PharmaFEFO\Enum\BatchStatus;

class StockBatchService
{
    public static function getDaysUntilExpiration(StockBatch $batch): int
    {
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $expiration = clone $batch->getExpirationDate();
        $expiration->setTime(0, 0, 0);

        $diff = $today->diff($expiration);

        // If expiration date is in the past, return negative number
        if ($expiration < $today) {
            return -1 * $diff->days;
        }

        // Return days until expiration (positive number or 0)
        return (int)$diff->days;
    }

    public static function isExpired(StockBatch $batch): bool
    {
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        return $batch->getExpirationDate() < $today;
    }

    public static function getCriticalityLevel(StockBatch $batch): string
    {
        $days = self::getDaysUntilExpiration($batch);

        if ($days < 0) {
            return 'expired';
        } elseif ($days <= 30) {
            return 'critical';
        } elseif ($days <= 90) {
            return 'warning';
        } else {
            return 'active';
        }
    }


    public static function getCriticalityClass(StockBatch $batch): string
    {
        $days = self::getDaysUntilExpiration($batch);

        if ($days < 0) {
            return 'expired';
        } elseif ($days <= 30) {
            return 'critical';
        } elseif ($days <= 90) {
            return 'warning';
        } else {
            return 'active';
        }
    }


    public static function getCriticalityColor(StockBatch $batch): string
    {
        $days = self::getDaysUntilExpiration($batch);

        if ($days < 0) {
            return 'gray';
        } elseif ($days <= 30) {
            return 'red';
        } elseif ($days <= 90) {
            return 'orange';
        } else {
            return 'green';
        }
    }


    public static function getStatusLabel(StockBatch $batch): string
    {
        $days = self::getDaysUntilExpiration($batch);

        if ($days < 0) {
            return 'Expired';
        } elseif ($days <= 30) {
            return 'Critical (< 30 days)';
        } elseif ($days <= 90) {
            return 'Warning (< 90 days)';
        } else {
            return 'Healthy (> 90 days)';
        }
    }


    public static function getBadgeClass(StockBatch $batch): string
    {
        $days = self::getDaysUntilExpiration($batch);

        if ($days < 0) {
            return 'bg-gray-100 text-gray-800 border-gray-200';
        } elseif ($days <= 30) {
            return 'bg-red-100 text-red-800 border-red-200';
        } elseif ($days <= 90) {
            return 'bg-amber-100 text-amber-800 border-amber-200';
        } else {
            return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        }
    }


    public static function getTotalValue(StockBatch $batch): float
    {
        return $batch->getQuantity() * $batch->getPurchasePrice();
    }

    public static function getTotalValueForBatches(array $batches): float
    {
        $total = 0;
        foreach ($batches as $batch) {
            $total += self::getTotalValue($batch);
        }
        return $total;
    }


    public static function needsReordering(StockBatch $batch, int $threshold = 10): bool
    {
        return $batch->getQuantity() <= $threshold;
    }


    public static function getBatchStatistics(array $batches): array
    {
        $critical = [];
        $warning = [];
        $healthy = [];
        $expired = [];

        foreach ($batches as $batch) {
            $days = self::getDaysUntilExpiration($batch);

            if ($days < 0) {
                $expired[] = $batch;
            } elseif ($days <= 30) {
                $critical[] = $batch;
            } elseif ($days <= 90) {
                $warning[] = $batch;
            } else {
                $healthy[] = $batch;
            }
        }

        return [
            'critical' => $critical,
            'warning' => $warning,
            'healthy' => $healthy,
            'expired' => $expired,
            'criticalCount' => count($critical),
            'warningCount' => count($warning),
            'healthyCount' => count($healthy),
            'expiredCount' => count($expired),
            'totalBatches' => count($batches)
        ];
    }


    public static function updateStatusByExpiration(StockBatch $batch): BatchStatus
    {
        $days = self::getDaysUntilExpiration($batch);

        if ($days < 0) {
            return BatchStatus::EXPIRED;
        } elseif ($days <= 30) {
            return BatchStatus::CRITICAL;
        } elseif ($days <= 90) {
            return BatchStatus::WARNING;
        } else {
            return BatchStatus::ACTIVE;
        }
    }

    public static function formatExpirationDate(StockBatch $batch, string $format = 'M d, Y'): string
    {
        return $batch->getExpirationDate()->format($format);
    }

    public static function isReturnable(StockBatch $batch, int $daysThreshold = 60): bool
    {
        $days = self::getDaysUntilExpiration($batch);
        return $days > 0 && $days <= $daysThreshold;
    }
}