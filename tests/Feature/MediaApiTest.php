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
        $this->seed();

        $response = $this->getJson('/api/videos?sort=trending');
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
    }
}
