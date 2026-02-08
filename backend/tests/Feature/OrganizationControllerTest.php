<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_organizations_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['name' => 'Acme']);
        $org->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAsSanctum($user)->getJson('/api/organizations');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'Acme');
    }

    public function test_index_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/organizations');

        $response->assertStatus(401);
    }

    public function test_store_creates_organization(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsSanctum($user)->postJson('/api/organizations', [
            'name' => 'New Org',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Org');

        $this->assertDatabaseHas('organizations', ['name' => 'New Org']);
        $org = Organization::where('name', 'New Org')->first();
        $this->assertTrue($org->users()->where('user_id', $user->id)->wherePivot('role', 'owner')->exists());
    }

    public function test_store_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsSanctum($user)->postJson('/api/organizations', [
            'name' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_show_returns_organization_when_member(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['name' => 'Acme']);
        $org->users()->attach($user->id, ['role' => 'member']);

        $response = $this->actingAsSanctum($user)->getJson('/api/organizations/' . $org->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Acme');
    }

    public function test_show_returns_403_when_not_member(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $otherUser = User::factory()->create();
        $org->users()->attach($otherUser->id, ['role' => 'owner']);

        $response = $this->actingAsSanctum($user)->getJson('/api/organizations/' . $org->id);

        $response->assertStatus(403);
    }

    public function test_update_succeeds_when_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['name' => 'Old Name']);
        $org->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAsSanctum($user)->putJson('/api/organizations/' . $org->id, [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('organizations', ['id' => $org->id, 'name' => 'Updated Name']);
    }

    public function test_update_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['name' => 'Acme']);
        $org->users()->attach($user->id, ['role' => 'member']);

        $response = $this->actingAsSanctum($user)->putJson('/api/organizations/' . $org->id, [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('organizations', ['id' => $org->id, 'name' => 'Acme']);
    }

    public function test_destroy_succeeds_when_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAsSanctum($user)->deleteJson('/api/organizations/' . $org->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
    }

    public function test_destroy_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user->id, ['role' => 'member']);

        $response = $this->actingAsSanctum($user)->deleteJson('/api/organizations/' . $org->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('organizations', ['id' => $org->id]);
    }
}
