<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationMemberRequest;
use App\Http\Requests\UpdateOrganizationMemberRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrganizationMemberController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a member in an organization.
     */
    public function store(StoreOrganizationMemberRequest $request, Organization $organization)
    {
        $this->authorize('manageMembers', $organization);

        $organization->users()->attach(
            $request->user_id,
            ['role' => $request->role]
        );

        return response()->json(['message' => 'Member added to organization']);
    }

    /**
     * Update the role of a member in an organization.
     */
    public function update(UpdateOrganizationMemberRequest $request, Organization $organization, User $user)
    {
        $this->authorize('manageMembers', $organization);

        $organization->users()
            ->updateExistingPivot($user->id, [
                'role' => $request->role,
            ]);

        return response()->json(['message' => 'Role updated']);
    }

    /**
     * Remove a member from an organization.
     */
    public function destroy(Organization $organization, User $user)
    {
        $this->authorize('manageMembers', $organization);

        $organization->users()->detach($user->id);

        return response()->json(['message' => 'Member removed from organization']);
    }
}
