<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\TrackResource;
use App\Http\Resources\VideoResource;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Track;
use App\Models\Video;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    protected function resolveModel(string $type, int $id)
    {
        return match ($type) {
            'video' => Video::findOrFail($id),
            'track' => Track::findOrFail($id),
            'article' => Article::findOrFail($id),
            default => abort(404),
        };
    }

    public function toggle(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type, $id);
        $userId = $request->user()->id;

        $existing = $model->bookmarks()->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false, 'message' => 'Removed from bookmarks']);
        }

        $model->bookmarks()->create(['user_id' => $userId]);
        return response()->json(['bookmarked' => true, 'message' => 'Saved to bookmarks']);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $bookmarks = Bookmark::where('user_id', $userId)->latest()->get();

        $videos = [];
        $tracks = [];
        $articles = [];

        foreach ($bookmarks as $bookmark) {
            if ($bookmark->bookmarkable_type === Video::class && $bookmark->bookmarkable) {
                $videos[] = new VideoResource($bookmark->bookmarkable);
            } elseif ($bookmark->bookmarkable_type === Track::class && $bookmark->bookmarkable) {
                $tracks[] = new TrackResource($bookmark->bookmarkable);
            } elseif ($bookmark->bookmarkable_type === Article::class && $bookmark->bookmarkable) {
                $articles[] = new ArticleResource($bookmark->bookmarkable);
            }
        }

        return response()->json([
            'bookmarks' => [
                'videos' => $videos,
                'tracks' => $tracks,
                'articles' => $articles,
            ],
        ]);
    }
}
