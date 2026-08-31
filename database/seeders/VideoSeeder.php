<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Video;
use App\Services\LiveDataFetcher;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::create([
            'name' => 'Media Creator',
            'email' => 'creator@afce.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Try fetching live video data from Deezer API (free, no key)
        $fetcher = new LiveDataFetcher();
        $liveVideos = $fetcher->fetchVideos(10);

        if (count($liveVideos) > 0) {
            $this->command->info("⬇ Fetched " . count($liveVideos) . " videos from Deezer API");

            foreach ($liveVideos as $videoData) {
                Video::create(array_merge($videoData, ['user_id' => $user->id]));
            }
        } else {
            $this->command->warn("⚠ Deezer API unavailable — using fallback videos");

            $fallbackVideos = [
                [
                    'title' => 'AFCE Annual Cultural Highlights 2026',
                    'description' => 'A showcase of African cultural festival events, performances, and celebrations.',
                    'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                    'thumbnail_url' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=800',
                    'duration' => 600,
                    'category' => 'Culture',
                    'views' => 12500,
                    'status' => 'published',
                    'published_at' => now()->subDays(2),
                ],
                [
                    'title' => 'Afrobeats Behind the Scenes - Studio Session',
                    'description' => 'Exclusive behind-the-scenes recording session with top producers.',
                    'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                    'thumbnail_url' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=800',
                    'duration' => 340,
                    'category' => 'Music',
                    'views' => 48200,
                    'status' => 'published',
                    'published_at' => now()->subHours(12),
                ],
                [
                    'title' => 'Tech in Africa: Building Next-Gen Platforms',
                    'description' => 'In-depth documentary on startups and digital innovation across the continent.',
                    'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                    'thumbnail_url' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=800',
                    'duration' => 1200,
                    'category' => 'Technology',
                    'views' => 8900,
                    'status' => 'published',
                    'published_at' => now()->subDays(5),
                ],
                [
                    'title' => 'African Film Festival Award Ceremony Highlights',
                    'description' => 'Red carpet moments and award speeches from the annual film festival.',
                    'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                    'thumbnail_url' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=800',
                    'duration' => 850,
                    'category' => 'Entertainment',
                    'views' => 31000,
                    'status' => 'published',
                    'published_at' => now()->subDays(1),
                ],
                [
                    'title' => 'Draft Video - Upcoming Release',
                    'description' => 'Unreleased preview video under edit.',
                    'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                    'thumbnail_url' => 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=800',
                    'duration' => 200,
                    'category' => 'Entertainment',
                    'views' => 0,
                    'status' => 'draft',
                    'published_at' => null,
                ],
            ];

            foreach ($fallbackVideos as $videoData) {
                Video::create(array_merge($videoData, ['user_id' => $user->id]));
            }
        }

        $this->command->info("✔ Total videos seeded: " . Video::count());
    }
}
