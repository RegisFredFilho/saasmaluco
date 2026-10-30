<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Enviada',
            self::UnderReview => 'Em análise',
            self::Accepted => 'Aceita',
            self::Rejected => 'Recusada',
            self::Withdrawn => 'Retirada',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Accepted, self::Rejected, self::Withdrawn], true);
    }

    /** @return list<self> */
    public function allowedVenueTransitions(): array
    {
        return match ($this) {
            self::Submitted => [self::UnderReview, self::Accepted, self::Rejected],
            self::UnderReview => [self::Accepted, self::Rejected],
            self::Accepted, self::Rejected, self::Withdrawn => [],
        };
    }

    public function canBeWithdrawnByArtist(): bool
    {
        return in_array($this, [self::Submitted, self::UnderReview], true);
    }
}
