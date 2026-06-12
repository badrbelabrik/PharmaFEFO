<?php

declare(strict_types=1);

namespace PharmaFEFO\Enum;

enum BatchStatus: string
{
    case OK = 'ok';
    case WARNING = 'warning';
    case CRITICAL = 'critical';
    case EXPIRED = 'expired';
    case RETURNED = 'returned_process';

    public function getColor(): string
    {
        return match($this) {
            self::OK => 'green',
            self::WARNING => 'orange',
            self::CRITICAL => 'red',
            self::EXPIRED => 'darkred',
            self::RETURNED => 'gray',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::OK => 'Actif / Sain',
            self::WARNING => 'Attention (< 90 jours)',
            self::CRITICAL => 'Critique (< 30 jours)',
            self::EXPIRED => 'Périmé',
            self::RETURNED => 'Retourné',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::OK => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::WARNING => 'bg-amber-100 text-amber-800 border-amber-200',
            self::CRITICAL => 'bg-red-100 text-red-800 border-red-200',
            self::EXPIRED => 'bg-gray-100 text-gray-800 border-gray-200',
            self::RETURNED => 'bg-purple-100 text-purple-800 border-purple-200',
        };
    }
}