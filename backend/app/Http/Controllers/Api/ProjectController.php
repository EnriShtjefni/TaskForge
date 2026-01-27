<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    private $projectService;

    public function __construct(ProjectService $projectService) {
        $this->projectService = $projectService;
    }

    /**
     * List projects for an organization
     */
    public function index(Organization $organization)
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->organizationsProjects($organization);

        return ProjectResource::collection($projects);
    }

    /**
     * Store new project
     */
    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', [Project::class, $request->organization_id]);

        $project = $this->projectService->create($request->validated());

        return new ProjectResource($project);
    }

    /**
     * Show a project
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project);
    }

    /**
     * Update a project
     */
    public function update(StoreProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $project = $this->projectService->update($project, $request->validated());

        return new ProjectResource($project);
    }

    /**
     * Delete a project
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $this->projectService->delete($project);

        return response()->json(['message' => 'Project deleted']);
    }
}
