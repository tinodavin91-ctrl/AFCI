<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches seed data from free public third-party APIs.
 *
 * APIs used:
 * - Deezer (tracks/music)      — https://api.deezer.com  (free, no key)
 * - RandomUser (seed users)     — https://randomuser.me   (free, no key)
 * - GNews (articles/news)       — https://gnews.io        (free tier, key required)
 * - Pexels (video thumbnails)   — https://api.pexels.com  (free, key required)
 */
class LiveDataFetcher
{
    /**
     * Fetch Afrobeats / African music tracks from Deezer.
     * Deezer search API is free and requires no authentication.
     */
    public function fetchTracks(int $limit = 15): array
    {
        $queries = ['afrobeats', 'amapiano', 'highlife', 'afro soul', 'naija music'];
        $tracks = [];

        foreach ($queries as $query) {
            try {
                $response = Http::timeout(10)
                    ->get('https://api.deezer.com/search', [
                        'q' => $query,
                        'limit' => 5,
                    ]);

                if ($response->successful()) {
                    foreach ($response->json('data', []) as $item) {
                        $tracks[] = [
                            'title' => $item['title'] ?? 'Untitled',
                            'artist' => $item['artist']['name'] ?? 'Unknown Artist',
                            'album' => $item['album']['title'] ?? null,
                            'audio_url' => $item['preview'] ?? '', // Deezer 30s preview MP3
                            'cover_art_url' => $item['album']['cover_big'] ?? $item['album']['cover'] ?? null,
                            'duration' => $item['duration'] ?? 0,
                            'genre' => $this->mapDeezerGenre($query),
                            'plays' => rand(500, 150000),
                            'status' => 'published',
                            'published_at' => now()->subDays(rand(0, 30)),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("LiveDataFetcher: Deezer search failed for '{$query}': " . $e->getMessage());
            }
        }

        return array_slice($tracks, 0, $limit);
    }

    /**
     * Fetch African music videos from Deezer (track data used to represent videos).
     * We use Deezer chart + search because it's free and gives album art for thumbnails.
     */
    public function fetchVideos(int $limit = 10): array
    {
        $queries = ['african music video', 'afrobeats official', 'nigerian music', 'south african music', 'african dance'];
        $videos = [];

        foreach ($queries as $query) {
            try {
                $response = Http::timeout(10)
                    ->get('https://api.deezer.com/search', [
                        'q' => $query,
                        'limit' => 3,
                    ]);

                if ($response->successful()) {
                    foreach ($response->json('data', []) as $item) {
                        $artistName = $item['artist']['name'] ?? 'Artist';
                        $trackTitle = $item['title'] ?? 'Untitled';

                        $videos[] = [
                            'title' => "{$artistName} - {$trackTitle} (Official Video)",
                            'description' => "Official music video for \"{$trackTitle}\" by {$artistName}. Stream now on AFCE Media.",
                            'video_url' => $item['preview'] ?? 'https://www.w3schools.com/html/mov_bbb.mp4',
                            'thumbnail_url' => $item['album']['cover_xl'] ?? $item['album']['cover_big'] ?? 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=800',
                            'duration' => $item['duration'] ?? 0,
                            'category' => 'Music',
                            'views' => rand(1000, 500000),
                            'status' => 'published',
                            'published_at' => now()->subDays(rand(0, 14)),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("LiveDataFetcher: Deezer video search failed for '{$query}': " . $e->getMessage());
            }
        }

        return array_slice($videos, 0, $limit);
    }

    /**
     * Fetch African entertainment news articles from GNews.
     * Requires a free API key (GNEWS_API_KEY in .env).
     * Free tier: 100 requests/day, 10 articles/request.
     */
    public function fetchArticles(int $limit = 10): array
    {
        $apiKey = config('services.gnews.api_key');
        $articles = [];

        $queries = ['african entertainment', 'afrobeats music', 'african film industry'];

        foreach ($queries as $query) {
            try {
                $response = Http::timeout(10)
                    ->get('https://gnews.io/api/v4/search', [
                        'q' => $query,
                        'lang' => 'en',
                        'max' => 5,
                        'apikey' => $apiKey,
                    ]);

                if ($response->successful()) {
                    foreach ($response->json('articles', []) as $item) {
                        $articles[] = [
                            'title' => $item['title'] ?? 'Untitled Article',
                            'excerpt' => $item['description'] ?? '',
                            'body' => $item['content'] ?? $item['description'] ?? '',
                            'cover_image_url' => $item['image'] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800',
                            'category' => $this->mapArticleCategory($query),
                            'views' => rand(200, 80000),
                            'status' => 'published',
                            'published_at' => isset($item['publishedAt']) ? \Carbon\Carbon::parse($item['publishedAt']) : now()->subDays(rand(0, 7)),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("LiveDataFetcher: GNews search failed for '{$query}': " . $e->getMessage());
            }
        }

        // If no API key or all requests fail, return empty — the seeder has fallback data.
        return array_slice($articles, 0, $limit);
    }

    /**
     * Fetch realistic seed users from RandomUser API.
     * Completely free, no authentication required.
     */
    public function fetchUsers(int $limit = 8): array
    {
        try {
            $response = Http::timeout(10)
                ->get('https://randomuser.me/api/', [
                    'results' => $limit,
                    'nat' => 'us,gb', // RandomUser doesn't support African locales directly
                    'inc' => 'name,email,picture,login',
                ]);

            if ($response->successful()) {
                return collect($response->json('results', []))->map(function ($user) {
                    return [
                        'name' => ($user['name']['first'] ?? 'User') . ' ' . ($user['name']['last'] ?? ''),
                        'email' => $user['email'] ?? ($user['login']['username'] ?? 'user') . '@afce.com',
                        'password' => bcrypt('password123'),
                        'role' => 'user',
                    ];
                })->all();
            }
        } catch (\Throwable $e) {
            Log::warning('LiveDataFetcher: RandomUser API failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Map Deezer search query to a genre label.
     */
    private function mapDeezerGenre(string $query): string
    {
        return match (true) {
            str_contains($query, 'amapiano') => 'Amapiano',
            str_contains($query, 'highlife') => 'Highlife',
            str_contains($query, 'soul') => 'Afro Soul',
            str_contains($query, 'naija') => 'Afrobeats',
            default => 'Afrobeats',
        };
    }

    /**
     * Map article search query to a category.
     */
    private function mapArticleCategory(string $query): string
    {
        return match (true) {
            str_contains($query, 'film') => 'Entertainment',
            str_contains($query, 'music') => 'Music',
            default => 'Culture',
        };
    }
}
