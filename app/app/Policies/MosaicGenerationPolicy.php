<?php

namespace App\Policies;

use App\Models\MosaicGeneration;
use App\Models\User;

class MosaicGenerationPolicy
{
    public function view(User $user, MosaicGeneration $generation): bool
    {
        return $user->isActive() && (int) $generation->user_id === (int) $user->id;
    }

    public function download(User $user, MosaicGeneration $generation): bool
    {
        return $this->view($user, $generation);
    }
}
