<?php

namespace Tests;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait SanctumAuthenticates
{
    /**
     * Authenticate as the given user for API requests (Sanctum token).
     */
    protected function actingAsSanctum(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }
}
