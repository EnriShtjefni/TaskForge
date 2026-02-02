<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    use AuthorizesRequests;
    private $organizationService;
    public function __construct(OrganizationService $organizationService)
    {
        $this->organizationService = $organizationService;
    }

    /**
     * List organizations for authenticated user
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Organization::class);

        $organizations = $this->organizationService
            ->usersOrganizations($request->user());

        return OrganizationResource::collection($organizations->load('users'));
    }

    /**
     * Create organization
     */
    public function store(StoreOrganizationRequest $request)
    {
        $this->authorize('create', Organization::class);

        $organization = $this->organizationService->create(
            $request->user(),
            $request->validated()
        );

        if ($request->filled('members')) {
            foreach ($request->members as $member) {
                $organization->users()->attach(
                    $member['user_id'],
                    ['role' => $member['role']]
                );
            }
        }

        return new OrganizationResource(
            $organization->load('users')
        );
    }

    /**
     * Show organization
     * @throws AuthorizationException
     */
    public function show(Organization $organization)
    {
        $this->authorize('view', $organization);

        return new OrganizationResource($organization->load('users'));
    }

    /**
     * Update organization
     * @throws AuthorizationException
     */
    public function update(StoreOrganizationRequest $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $organization = $this->organizationService->update(
            $organization,
            $request->validated()
        );

        if ($request->has('members')) {
            $organization->users()
                ->wherePivot('role', '!=', 'owner')
                ->detach();

            foreach ($request->members as $member) {
                $organization->users()->attach(
                    $member['user_id'],
                    ['role' => $member['role']]
                );
            }
        }

        return new OrganizationResource(
            $organization->load('users')
        );
    }

    /**
     * Delete organization
     * @throws AuthorizationException
     */
    public function destroy(Organization $organization)
    {
        $this->authorize('delete', $organization);

        $this->organizationService->delete($organization);

        return response()->json(['message' => 'Organization deleted']);
    }
}
