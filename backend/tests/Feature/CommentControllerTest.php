<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createTaskWithProjectMember(): array
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'member']);
        $project = Project::factory()->create(['organization_id' => $org->id]);
        $project->users()->attach($user->id);
        $task = Task::factory()->create(['project_id' => $project->id]);

        return ['user' => $user, 'project' => $project, 'task' => $task];
    }

    public function test_store_creates_comment_when_project_member(): void
    {
        $data = $this->createTaskWithProjectMember();

        $response = $this->actingAsSanctum($data['user'])->postJson('/api/comments', [
            'task_id' => $data['task']->id,
            'body' => 'A comment',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.body', 'A comment');

        $this->assertDatabaseHas('comments', [
            'task_id' => $data['task']->id,
            'user_id' => $data['user']->id,
            'body' => 'A comment',
        ]);
    }

    public function test_store_returns_403_when_not_project_member(): void
    {
        $data = $this->createTaskWithProjectMember();
        $otherUser = User::factory()->create();

        $response = $this->actingAsSanctum($otherUser)->postJson('/api/comments', [
            'task_id' => $data['task']->id,
            'body' => 'A comment',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('comments', ['task_id' => $data['task']->id, 'body' => 'A comment']);
    }

    public function test_store_fails_validation_without_body(): void
    {
        $data = $this->createTaskWithProjectMember();

        $response = $this->actingAsSanctum($data['user'])->postJson('/api/comments', [
            'task_id' => $data['task']->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_destroy_succeeds_when_comment_author(): void
    {
        $data = $this->createTaskWithProjectMember();
        $comment = Comment::factory()->create([
            'task_id' => $data['task']->id,
            'user_id' => $data['user']->id,
            'body' => 'My comment',
        ]);

        $response = $this->actingAsSanctum($data['user'])->deleteJson('/api/comments/' . $comment->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_destroy_returns_403_when_not_comment_author(): void
    {
        $data = $this->createTaskWithProjectMember();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'task_id' => $data['task']->id,
            'user_id' => $data['user']->id,
            'body' => 'My comment',
        ]);

        $response = $this->actingAsSanctum($otherUser)->deleteJson('/api/comments/' . $comment->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}
