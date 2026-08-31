<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\TrackResource;
use App\Http\Resources\VideoResource;
use App\Models\Article;
use App\Models\Track;
use App\Models\Video;
use Illuminate\Http\Request;

class TrendingController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', 5);
        $type = $request->get('type');

        $trendingVideos = Video::published()->orderByDesc('views')->take($limit)->get();
        $trendingTracks = Track::published()->orderByDesc('plays')->take($limit)->get();
        $trendingArticles = Article::published()->orderByDesc('views')->take($limit)->get();

        if ($type === 'video') {
            return VideoResource::collection($trendingVideos);
        }

        if ($type === 'track') {
            return TrackResource::collection($trendingTracks);
        }

        if ($type === 'article') {
            return ArticleResource::collection($trendingArticles);
        }

        return response()->json([
            'trending' => [
                'videos' => VideoResource::collection($trendingVideos),
                'tracks' => TrackResource::collection($trendingTracks),
                'articles' => ArticleResource::collection($trendingArticles),
            ],
        ]);
    }
}
