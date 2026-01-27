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

        return OrganizationResource::collection($organizations);
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

        return new OrganizationResource($organization);
    }

    /**
     * Show organization
     * @throws AuthorizationException
     */
    public function show(Organization $organization)
    {
        $this->authorize('view', $organization);

        return new OrganizationResource($organization);
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

        return new OrganizationResource($organization);
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
