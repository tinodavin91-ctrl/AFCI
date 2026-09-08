<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeding_and_trending_endpoint(): void
    {
        $this->seed();

        $response = $this->getJson('/api/trending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trending' => [
                    'videos',
                    'tracks',
                    'articles',
                ],
            ]);
    }

    public function test_categories_and_genres_endpoints(): void
    {
        $this->seed();

        $categoriesResponse = $this->getJson('/api/categories');
        $categoriesResponse->assertStatus(200)
            ->assertJsonStructure(['categories']);

        $genresResponse = $this->getJson('/api/genres');
        $genresResponse->assertStatus(200)
            ->assertJsonStructure(['genres']);
    }

    public function test_video_filtering_and_trending_sort(): void
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Video::create([
            'user_id' => $user->id,
            'title' => 'African Culture Spotlight',
            'video_url' => 'https://example.com/video.mp4',
            'category' => 'Culture',
            'views' => 1200,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/videos?sort=trending');
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
    }
}
