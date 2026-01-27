<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->organization
            ->users()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $organizationId): bool
    {
        return $user->organizations()
            ->where('organizations.id', $organizationId)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $project->organization
            ->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->organization
            ->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can manage project members.
     */
    public function manage(User $user, Project $project): bool
    {
        return $project->organization
            ->users()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', ['owner'])
            ->exists();
    }

    /**
     * Determine whether the user can add a member to the project.
     */
    public function addMemberToProject(User $user, Project $project, User $member): bool
    {
        // Only owners can manage project members
        if (! $project->organization
            ->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists()) {
            return false;
        }

        // The added user must belong to the organization
        return $project->organization
            ->users()
            ->where('users.id', $member->id)
            ->exists();
    }


    /**
     * Determine whether the user can comment on the project.
     */
    public function comment(User $user, Project $project): bool
    {
        return $project->users()
            ->where('users.id', $user->id)
            ->exists();
    }

}
