<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use App\Services\LiveDataFetcher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::create([
            'name' => 'Media Creator',
            'email' => 'creator@afce.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Try fetching live articles from GNews API (free tier, key required)
        $fetcher = new LiveDataFetcher();
        $liveArticles = $fetcher->fetchArticles(10);

        if (count($liveArticles) > 0) {
            $this->command->info("⬇ Fetched " . count($liveArticles) . " articles from GNews API");

            foreach ($liveArticles as $articleData) {
                Article::create(array_merge($articleData, [
                    'user_id' => $user->id,
                    'slug' => Str::slug($articleData['title']),
                ]));
            }
        } else {
            $this->command->warn("⚠ GNews API unavailable (set GNEWS_API_KEY in .env) — using fallback articles");

            $fallbackArticles = [
                [
                    'title' => 'African Creative Ecosystem Expands Beyond Borders in 2026',
                    'excerpt' => 'How digital streaming and web technologies are accelerating global distribution of African film, music, and art.',
                    'body' => 'The African creative economy is witnessing unprecedented growth. Across music, cinema, and digital journalism, creators are utilizing modern streaming APIs and decentralized distribution platforms to reach millions worldwide. From Nollywood to Afrobeats, the continent\'s cultural output is reshaping global entertainment consumption patterns.',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800',
                    'category' => 'Culture',
                    'views' => 15400,
                    'status' => 'published',
                    'published_at' => now()->subHours(5),
                ],
                [
                    'title' => 'Top 10 Emerging Tech Hubs Across the Continent',
                    'excerpt' => 'A breakdown of key technology cities driving innovation in mobile apps, fintech, and digital media.',
                    'body' => 'From Nairobi to Lagos, Cape Town to Cairo, tech hubs are transforming local economies. Founders and developers are launching platforms that solve regional challenges while competing on the global stage. Investment in African tech startups hit record levels in 2025, with fintech and media-tech leading the charge.',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800',
                    'category' => 'Technology',
                    'views' => 32900,
                    'status' => 'published',
                    'published_at' => now()->subDays(1),
                ],
                [
                    'title' => 'Afrobeats Takes Center Stage at Global Music Summit',
                    'excerpt' => 'Industry leaders gather to discuss streaming revenue models and global artist partnerships.',
                    'body' => 'Music executives and artists convened this week to highlight the explosive rise of Afrobeats on global streaming charts. The summit focused on fair compensation, digital rights management, and collaborative production. Key discussions centered on how African artists can retain ownership while maximizing reach.',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=800',
                    'category' => 'Entertainment',
                    'views' => 61200,
                    'status' => 'published',
                    'published_at' => now()->subDays(4),
                ],
                [
                    'title' => 'The Rise of African Streaming Platforms',
                    'excerpt' => 'Local platforms challenge global giants with culturally relevant content and pricing models.',
                    'body' => 'While Netflix and Spotify dominate globally, homegrown African platforms are carving out significant market share by offering content that resonates deeply with local audiences. From curated Nollywood catalogs to exclusive Amapiano playlists, these platforms understand their audience in ways international competitors cannot.',
                    'cover_image_url' => 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=800',
                    'category' => 'Technology',
                    'views' => 24700,
                    'status' => 'published',
                    'published_at' => now()->subDays(2),
                ],
            ];

            foreach ($fallbackArticles as $articleData) {
                Article::create(array_merge($articleData, [
                    'user_id' => $user->id,
                    'slug' => Str::slug($articleData['title']),
                ]));
            }
        }

        $this->command->info("✔ Total articles seeded: " . Article::count());
    }
}
