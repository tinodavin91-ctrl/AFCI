<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $userVideos = Video::where('user_id', $user->id)->get();
        $userTracks = Track::where('user_id', $user->id)->get();
        $userArticles = Article::where('user_id', $user->id)->get();

        $totalVideoViews = $userVideos->sum('views');
        $totalTrackPlays = $userTracks->sum('plays');
        $totalArticleViews = $userArticles->sum('views');

        $totalLikesReceived = $userVideos->sum(fn ($v) => $v->likes()->count())
            + $userTracks->sum(fn ($t) => $t->likes()->count())
            + $userArticles->sum(fn ($a) => $a->likes()->count());

        return response()->json([
            'user' => $user,
            'stats' => [
                'total_video_views' => $totalVideoViews,
                'total_track_plays' => $totalTrackPlays,
                'total_article_views' => $totalArticleViews,
                'total_likes_received' => $totalLikesReceived,
                'videos_count' => $userVideos->count(),
                'tracks_count' => $userTracks->count(),
                'articles_count' => $userArticles->count(),
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
