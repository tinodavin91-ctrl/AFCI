<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaylistsAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_playlist_creation_and_track_management(): void
    {
        $user = User::factory()->create();
        $track = Track::create([
            'user_id' => $user->id,
            'title' => 'Sample Track',
            'artist' => 'Artist',
            'audio_url' => 'https://example.com/song.mp3',
            'status' => 'published',
        ]);

        // Create playlist
        $createResponse = $this->actingAs($user, 'sanctum')
            ->postJson('/api/playlists', [
                'title' => 'Afrobeats Favorites',
                'description' => 'Best tracks of the year',
                'is_public' => true,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('title', 'Afrobeats Favorites');

        $playlistId = $createResponse->json('id');

        // Add track to playlist
        $addResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/playlists/{$playlistId}/tracks", [
                'track_id' => $track->id,
            ]);

        $addResponse->assertStatus(200);

        // Fetch playlist details
        $showResponse = $this->getJson("/api/playlists/{$playlistId}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('tracks.0.title', 'Sample Track');
    }

    public function test_like_triggers_notification(): void
    {
        $creator = User::factory()->create(['name' => 'Creator User']);
        $fan = User::factory()->create(['name' => 'Fan User']);

        $video = Video::create([
            'user_id' => $creator->id,
            'title' => 'Creator Video',
            'video_url' => 'https://example.com/video.mp4',
            'status' => 'published',
        ]);

        // Fan likes creator's video
        \Laravel\Sanctum\Sanctum::actingAs($fan);
        $likeResponse = $this->postJson("/api/video/{$video->id}/like");
        $likeResponse->assertStatus(200);

        // Assert notification created for creator
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $creator->id,
            'type' => 'like',
        ]);

        // Creator fetches notifications
        \Laravel\Sanctum\Sanctum::actingAs($creator);
        $notifResponse = $this->getJson('/api/notifications');

        $notifResponse->assertStatus(200)
            ->assertJsonPath('unread_count', 1);
    }

    public function test_admin_stats_and_authorization(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        $adminUser = User::factory()->create(['role' => 'admin']);

        // Regular user access denied
        $deniedResponse = $this->actingAs($regularUser, 'sanctum')
            ->getJson('/api/admin/stats');
        $deniedResponse->assertStatus(403);

        // Admin access granted
        $allowedResponse = $this->actingAs($adminUser, 'sanctum')
            ->getJson('/api/admin/stats');
        $allowedResponse->assertStatus(200)
            ->assertJsonStructure(['stats' => ['total_users', 'total_videos', 'total_tracks']]);
    }

    public function test_private_playlist_access_with_sanctum_auth(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $playlist = Playlist::create([
            'user_id' => $owner->id,
            'title' => 'Secret Playlist',
            'is_public' => false,
        ]);

        // Unauthenticated access fails
        $this->getJson("/api/playlists/{$playlist->id}")->assertStatus(403);

        // Stranger access fails
        $this->actingAs($stranger, 'sanctum')
            ->getJson("/api/playlists/{$playlist->id}")
            ->assertStatus(403);

        // Owner access succeeds
        $this->actingAs($owner, 'sanctum')
            ->getJson("/api/playlists/{$playlist->id}")
            ->assertStatus(200)
            ->assertJsonPath('title', 'Secret Playlist');
    }

    public function test_notification_deletion_returns_json(): void
    {
        $user = User::factory()->create();
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'Welcome',
            'message' => 'Welcome to AFCE Media',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification deleted',
            ]);

        $this->assertDatabaseMissing('app_notifications', ['id' => $notification->id]);
    }
}
