<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Article;
use App\Models\Track;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $videosQuery = Video::where('user_id', $user->id);
        $tracksQuery = Track::where('user_id', $user->id);
        $articlesQuery = Article::where('user_id', $user->id);

        $videosCount = (clone $videosQuery)->count();
        $tracksCount = (clone $tracksQuery)->count();
        $articlesCount = (clone $articlesQuery)->count();

        $totalVideoViews = (int) (clone $videosQuery)->sum('views');
        $totalTrackPlays = (int) (clone $tracksQuery)->sum('plays');
        $totalArticleViews = (int) (clone $articlesQuery)->sum('views');

        $videoIds = (clone $videosQuery)->pluck('id');
        $trackIds = (clone $tracksQuery)->pluck('id');
        $articleIds = (clone $articlesQuery)->pluck('id');

        $videoLikes = $videoIds->isNotEmpty() ? Like::where('likeable_type', Video::class)->whereIn('likeable_id', $videoIds)->count() : 0;
        $trackLikes = $trackIds->isNotEmpty() ? Like::where('likeable_type', Track::class)->whereIn('likeable_id', $trackIds)->count() : 0;
        $articleLikes = $articleIds->isNotEmpty() ? Like::where('likeable_type', Article::class)->whereIn('likeable_id', $articleIds)->count() : 0;

        $totalLikesReceived = $videoLikes + $trackLikes + $articleLikes;

        return response()->json([
            'user' => $user,
            'stats' => [
                'total_video_views' => $totalVideoViews,
                'total_track_plays' => $totalTrackPlays,
                'total_article_views' => $totalArticleViews,
                'total_likes_received' => $totalLikesReceived,
                'videos_count' => $videosCount,
                'tracks_count' => $tracksCount,
                'articles_count' => $articlesCount,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }
}
