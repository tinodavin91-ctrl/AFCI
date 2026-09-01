<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YoutubeService
{
    /**
     * Search YouTube for videos matching a keyword/topic.
     *
     * @return array<int, array{
     *     youtube_id: string,
     *     title: string,
     *     description: string,
     *     thumbnail_url: string|null,
     *     published_at: string,
     * }>
     */
    public function search(string $query, int $maxResults = 10): array
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
            'part' => 'snippet',
            'q' => $query,
            'type' => 'video',
            'maxResults' => $maxResults,
            'key' => config('services.youtube.key'),
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('items', []))
            ->map(function ($item) {
                return [
                    'youtube_id' => $item['id']['videoId'],
                    'title' => $item['snippet']['title'],
                    'description' => $item['snippet']['description'],
                    'thumbnail_url' => $item['snippet']['thumbnails']['high']['url'] ?? null,
                    'published_at' => $item['snippet']['publishedAt'],
                ];
            })
            ->toArray();
    }
}

