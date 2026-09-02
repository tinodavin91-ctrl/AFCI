<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\YoutubeService;
use Illuminate\Console\Command;

class SyncYoutubeVideos extends Command
{
    protected $signature = 'youtube:sync {query} {--count=10}';
    protected $description = 'Fetch videos from YouTube and store them';

    public function handle(YoutubeService $youtube)
    {
        $query = $this->argument('query');
        $count = (int) $this->option('count');

        $this->info("Searching YouTube for: {$query}");
        $results = $youtube->search($query, $count);

        $created = 0;
        foreach ($results as $item) {
            $video = Video::updateOrCreate(
                ['youtube_id' => $item['youtube_id']],
                [
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'video_url' => "https://www.youtube.com/watch?v={$item['youtube_id']}",
                    'thumbnail_url' => $item['thumbnail_url'],
                    'status' => 'published',
                    'published_at' => $item['published_at'],
                    'user_id' => 1,
                ]
            );
            if ($video->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->info("Done. {$created} new videos added, " . (count($results) - $created) . " updated.");
    }
}