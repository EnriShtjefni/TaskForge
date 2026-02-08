<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithMembers(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($owner->id, ['role' => 'owner']);
        $org->users()->attach($member->id, ['role' => 'member']);
        $project = Project::factory()->create(['organization_id' => $org->id]);
        $project->users()->attach([$owner->id, $member->id]);

        return ['owner' => $owner, 'member' => $member, 'org' => $org, 'project' => $project];
    }

    public function test_index_returns_tasks_for_project_member(): void
    {
        $data = $this->createProjectWithMembers();
        Task::factory()->count(2)->create(['project_id' => $data['project']->id]);

        $response = $this->actingAsSanctum($data['member'])
            ->getJson('/api/projects/' . $data['project']->id . '/tasks');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_returns_403_when_not_project_member(): void
    {
        $data = $this->createProjectWithMembers();
        $otherUser = User::factory()->create();

        $response = $this->actingAsSanctum($otherUser)
            ->getJson('/api/projects/' . $data['project']->id . '/tasks');

        $response->assertStatus(403);
    }

    public function test_store_creates_task_when_owner_or_manager(): void
    {
        $data = $this->createProjectWithMembers();

        $response = $this->actingAsSanctum($data['owner'])->postJson('/api/tasks', [
            'project_id' => $data['project']->id,
            'name' => 'New Task',
            'description' => 'Description',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Task');

        $this->assertDatabaseHas('tasks', ['name' => 'New Task', 'project_id' => $data['project']->id]);
        $task = Task::where('name', 'New Task')->first();
        $this->assertNotNull($task->status);
    }

    public function test_store_returns_403_when_member_not_manager_or_owner(): void
    {
        $data = $this->createProjectWithMembers();

        $response = $this->actingAsSanctum($data['member'])->postJson('/api/tasks', [
            'project_id' => $data['project']->id,
            'name' => 'New Task',
        ]);

        $response->assertStatus(403);
    }

    public function test_update_succeeds_when_owner_or_manager(): void
    {
        $data = $this->createProjectWithMembers();
        $task = Task::factory()->create(['project_id' => $data['project']->id, 'name' => 'Old Name']);

        $response = $this->actingAsSanctum($data['owner'])->putJson('/api/tasks/' . $task->id, [
            'name' => 'Updated Task',
            'description' => 'Updated desc',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Task');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'name' => 'Updated Task']);
    }

    public function test_update_status_succeeds_when_assigned_user(): void
    {
        $data = $this->createProjectWithMembers();
        $task = Task::factory()->create([
            'project_id' => $data['project']->id,
            'assigned_to' => $data['member']->id,
            'status' => 'todo',
        ]);

        $response = $this->actingAsSanctum($data['member'])->putJson('/api/tasks/' . $task->id . '/status', [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress']);
    }

    public function test_update_status_returns_403_when_not_assigned_user(): void
    {
        $data = $this->createProjectWithMembers();
        $task = Task::factory()->create([
            'project_id' => $data['project']->id,
            'assigned_to' => $data['member']->id,
            'status' => 'todo',
        ]);

        $response = $this->actingAsSanctum($data['owner'])->putJson('/api/tasks/' . $task->id . '/status', [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'todo']);
    }

    public function test_destroy_succeeds_when_owner_or_manager(): void
    {
        $data = $this->createProjectWithMembers();
        $task = Task::factory()->create(['project_id' => $data['project']->id]);

        $response = $this->actingAsSanctum($data['owner'])->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_destroy_returns_403_when_member_not_owner_or_manager(): void
    {
        $data = $this->createProjectWithMembers();
        $task = Task::factory()->create(['project_id' => $data['project']->id]);

        $response = $this->actingAsSanctum($data['member'])->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
