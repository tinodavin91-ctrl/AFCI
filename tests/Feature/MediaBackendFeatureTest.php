<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Track;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaBackendFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_upload_endpoint(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('cover.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/upload', ['file' => $file]);

        $response->assertStatus(201)
            ->assertJsonStructure(['url', 'path', 'original_name', 'mime_type']);
    }

    public function test_global_search_endpoint(): void
    {
        $this->seed();

        $response = $this->getJson('/api/search?q=African');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'results' => [
                    'videos',
                    'tracks',
                    'articles',
                ],
            ]);
    }

    public function test_bookmark_toggle_and_listing(): void
    {
        $user = User::factory()->create();
        $video = Video::create([
            'user_id' => $user->id,
            'title' => 'Test Video',
            'video_url' => 'https://example.com/video.mp4',
            'status' => 'published',
        ]);

        // Toggle bookmark ON
        $toggleResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/video/{$video->id}/bookmark");

        $toggleResponse->assertStatus(200)
            ->assertJson(['bookmarked' => true]);

        // Fetch bookmarks list
        $listResponse = $this->actingAs($user, 'sanctum')
            ->getJson('/api/bookmarks');

        $listResponse->assertStatus(200)
            ->assertJsonStructure(['bookmarks' => ['videos', 'tracks', 'articles']]);
    }

    public function test_user_profile_and_creator_stats(): void
    {
        $user = User::factory()->create();

        Video::create([
            'user_id' => $user->id,
            'title' => 'Sample Video',
            'video_url' => 'https://example.com/video.mp4',
            'views' => 500,
            'status' => 'published',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'stats' => [
                    'total_video_views',
                    'total_track_plays',
                    'total_article_views',
                    'total_likes_received',
                    'videos_count',
                    'tracks_count',
                    'articles_count',
                ],
            ])
            ->assertJsonPath('stats.total_video_views', 500);
    }
}
