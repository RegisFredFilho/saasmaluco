<?php

namespace App\Enums;

enum OpportunityStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    case Filled = 'filled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Open => 'Aberta',
            self::Closed => 'Encerrada',
            self::Cancelled => 'Cancelada',
            self::Filled => 'Preenchida',
        };
    }

    public function acceptsApplications(): bool
    {
        return $this === self::Open;
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Open, self::Cancelled],
            self::Open => [self::Closed, self::Cancelled, self::Filled],
            self::Closed, self::Cancelled, self::Filled => [],
        };
    }
}
