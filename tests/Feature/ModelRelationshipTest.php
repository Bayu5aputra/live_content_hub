<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Content;
use App\Models\Playlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_organizations()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();

        $user->organizations()->attach($organization->id, ['role' => 'admin']);

        $this->assertCount(1, $user->organizations);
        $this->assertEquals('admin', $user->organizations->first()->pivot->role);
    }

    public function test_organization_can_have_users()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();

        $organization->users()->attach($user->id, ['role' => 'editor']);

        $this->assertCount(1, $organization->users);
    }

    public function test_organization_can_have_contents()
    {
        $organization = Organization::factory()->create();
        $content = Content::factory()->create(['organization_id' => $organization->id]);

        $this->assertCount(1, $organization->contents);
    }

    public function test_organization_can_have_playlists()
    {
        $organization = Organization::factory()->create();
        $playlist = Playlist::factory()->create(['organization_id' => $organization->id]);

        $this->assertCount(1, $organization->playlists);
    }

    public function test_content_belongs_to_organization()
    {
        $organization = Organization::factory()->create();
        $content = Content::factory()->create(['organization_id' => $organization->id]);

        $this->assertEquals($organization->id, $content->organization->id);
    }

    public function test_playlist_can_have_contents()
    {
        $playlist = Playlist::factory()->create();
        $content = Content::factory()->create(['organization_id' => $playlist->organization_id]);

        $playlist->contents()->attach($content->id, ['order' => 1]);

        $this->assertCount(1, $playlist->contents);
    }
}
