<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_project_when_user_is_org_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAsSanctum($user)->postJson('/api/projects', [
            'name' => 'New Project',
            'organization_id' => $org->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Project');

        $this->assertDatabaseHas('projects', ['name' => 'New Project', 'organization_id' => $org->id]);
    }

    public function test_store_returns_403_when_user_is_not_org_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'member']);

        $response = $this->actingAsSanctum($user)->postJson('/api/projects', [
            'name' => 'New Project',
            'organization_id' => $org->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('projects', ['name' => 'New Project']);
    }

    public function test_store_fails_validation_without_name(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAsSanctum($user)->postJson('/api/projects', [
            'organization_id' => $org->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_show_returns_project_when_org_member(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'member']);
        $project = Project::factory()->create(['organization_id' => $org->id]);
        $project->users()->attach($user->id);

        $response = $this->actingAsSanctum($user)->getJson('/api/projects/' . $project->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', $project->name);
    }

    public function test_show_returns_403_when_not_org_member(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $otherUser = User::factory()->create();
        $org->users()->attach($otherUser->id, ['role' => 'owner']);
        $project = Project::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAsSanctum($user)->getJson('/api/projects/' . $project->id);

        $response->assertStatus(403);
    }

    public function test_update_succeeds_when_org_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'owner']);
        $project = Project::factory()->create(['organization_id' => $org->id, 'name' => 'Old Name']);

        $response = $this->actingAsSanctum($user)->putJson('/api/projects/' . $project->id, [
            'name' => 'Updated Name',
            'organization_id' => $org->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
    }

    public function test_update_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'member']);
        $project = Project::factory()->create(['organization_id' => $org->id, 'name' => 'Acme']);

        $response = $this->actingAsSanctum($user)->putJson('/api/projects/' . $project->id, [
            'name' => 'Hacked',
            'organization_id' => $org->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Acme']);
    }

    public function test_destroy_succeeds_when_org_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'owner']);
        $project = Project::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAsSanctum($user)->deleteJson('/api/projects/' . $project->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_destroy_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'member']);
        $project = Project::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAsSanctum($user)->deleteJson('/api/projects/' . $project->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
