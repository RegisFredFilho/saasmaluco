<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Proposal extends Model
{
    protected $fillable = [
        'conversation_id',
        'created_by_user_id',
        'amount',
        'currency',
        'conditions',
        'valid_until',
        'status',
        'version',
        'supersedes_id',
        'superseded_by_id',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'valid_until' => 'date',
            'status' => ProposalStatus::class,
            'version' => 'integer',
            'responded_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }
}
