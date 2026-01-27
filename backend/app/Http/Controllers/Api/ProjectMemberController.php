<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectMemberRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectMemberController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a member in a project.
     */
    public function store(StoreProjectMemberRequest $request, Project $project)
    {
        $member = User::findOrFail($request->user_id);

        $this->authorize('addMemberToProject', [$project, $member]);

        $project->users()->attach($request->user_id);

        return response()->json(['message' => 'User added to project']);
    }

    /**
     * Remove a member from a project.
     */
    public function destroy(Project $project, $userId)
    {
        $this->authorize('manage', $project);

        $project->users()->detach($userId);

        return response()->json(['message' => 'User removed from project']);
    }
}
