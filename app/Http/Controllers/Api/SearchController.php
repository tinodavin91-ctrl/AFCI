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

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (empty($query)) {
            return response()->json([
                'query' => '',
                'results' => [
                    'videos' => [],
                    'tracks' => [],
                    'articles' => [],
                ],
            ]);
        }

        $videos = Video::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            })
            ->latest('published_at')
            ->take(10)
            ->get();

        $tracks = Track::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('artist', 'like', "%{$query}%")
                    ->orWhere('album', 'like', "%{$query}%")
                    ->orWhere('genre', 'like', "%{$query}%");
            })
            ->latest('published_at')
            ->take(10)
            ->get();

        $articles = Article::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            })
            ->latest('published_at')
            ->take(10)
            ->get();

        return response()->json([
            'query' => $query,
            'results' => [
                'videos' => VideoResource::collection($videos),
                'tracks' => TrackResource::collection($tracks),
                'articles' => ArticleResource::collection($articles),
            ],
        ]);
    }
}
