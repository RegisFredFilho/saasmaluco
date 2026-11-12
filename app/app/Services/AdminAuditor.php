<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminAuditor
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function log(User $actor, string $action, ?Model $subject = null, array $payload = []): AdminAuditLog
    {
        return AdminAuditLog::query()->create([
            'actor_user_id' => $actor->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'payload' => $payload === [] ? null : $payload,
            'created_at' => now(),
        ]);
    }
}
