<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Like;
use App\Models\Track;
use App\Models\Video;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    protected function resolveModel(string $type, int $id)
    {
        $model = match ($type) {
            'video' => Video::findOrFail($id),
            'track' => Track::findOrFail($id),
            'article' => Article::findOrFail($id),
            default => abort(404),
        };

        return $model;
    }

    public function toggle(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type, $id);
        $userId = $request->user()->id;

        $existing = $model->likes()->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['liked' => false, 'likes_count' => $model->likes()->count()]);
        }

        $model->likes()->create(['user_id' => $userId]);
        return response()->json(['liked' => true, 'likes_count' => $model->likes()->count()]);
    }
}
