<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Superseded = 'superseded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Sent => 'Enviada',
            self::Accepted => 'Aceita',
            self::Rejected => 'Recusada',
            self::Expired => 'Expirada',
            self::Superseded => 'Substituída',
        };
    }

    public function isRespondable(): bool
    {
        return $this === self::Sent;
    }
}
