<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * List all projects for a given organization
     */
    public function organizationsProjects(Organization $organization): Collection
    {
        return $organization->projects()
            ->with('tasks')
            ->latest()
            ->get();
    }

    /**
     * Create project
     */
    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create($data);
            return $project;
        });
    }

    /**
     * Update project
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->refresh();
    }

    /**
     * Delete project
     */
    public function delete(Project $project): void
    {
        $project->delete();
    }
}
