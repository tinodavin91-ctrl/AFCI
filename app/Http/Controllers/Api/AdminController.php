<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected function authorizeAdmin(Request $request)
    {
        abort_unless($request->user() && $request->user()->isAdmin(), 403, 'Unauthorized. Admin role required.');
    }

    public function stats(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'stats' => [
                'total_users' => User::count(),
                'total_videos' => Video::count(),
                'total_tracks' => Track::count(),
                'total_articles' => Article::count(),
                'total_comments' => Comment::count(),
                'total_likes' => Like::count(),
                'total_video_views' => Video::sum('views'),
                'total_track_plays' => Track::sum('plays'),
                'total_article_views' => Article::sum('views'),
            ],
        ]);
    }

    public function users(Request $request)
    {
        $this->authorizeAdmin($request);

        $users = User::when($request->search, function ($q, $search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user->update(['role' => $validated['role']]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user,
        ]);
    }

    public function deleteContent(Request $request, string $type, int $id)
    {
        $this->authorizeAdmin($request);

        $model = match ($type) {
            'video' => Video::findOrFail($id),
            'track' => Track::findOrFail($id),
            'article' => Article::findOrFail($id),
            'comment' => Comment::findOrFail($id),
            default => abort(404),
        };

        $model->delete();

        return response()->json([
            'message' => ucfirst($type) . ' deleted by admin',
        ]);
    }
}
