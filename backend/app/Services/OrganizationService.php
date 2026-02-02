<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class OrganizationService
{
    /**
     * Get organizations for a user
     */
    public function usersOrganizations(User $user): Collection
    {
        return $user->organizations()
            ->with([
                'users:id,name,email',
            ])
            ->latest()
            ->get();
    }

    /**
     * Create organization and attach owner
     */
    public function create(User $user, array $data): Organization
    {
        return DB::transaction(function () use ($user, $data) {
            $organization = Organization::create($data);

            $organization->users()->attach($user->id, [
                'role' => 'owner',
            ]);

            return $organization->load('users');
        });
    }

    /**
     * Update organization
     */
    public function update(Organization $organization, array $data): Organization
    {
        $organization->update($data);

        return $organization->refresh();
    }

    /**
     * Delete organization
     */
    public function delete(Organization $organization): void
    {
        $organization->delete();
    }
}
