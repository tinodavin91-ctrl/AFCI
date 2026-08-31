<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Track;
use App\Models\Video;
use Illuminate\Http\Request;

class CommentController extends Controller
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

    public function index(string $type, int $id)
    {
        $model = $this->resolveModel($type, $id);
        $comments = $model->comments()->with('user')->latest()->paginate(15);
        return CommentResource::collection($comments);
    }

    public function store(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type, $id);

        $data = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $model->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        // Send notification to content creator if someone else commented
        if ($model->user_id && (int) $model->user_id !== (int) $request->user()->id) {
            \App\Models\AppNotification::create([
                'user_id' => $model->user_id,
                'type' => 'comment',
                'title' => 'New Comment',
                'message' => $request->user()->name . ' commented on your ' . $type . ': "' . ($model->title ?? 'item') . '".',
                'data' => ['type' => $type, 'id' => $id, 'comment_id' => $comment->id],
            ]);
        }

        return new CommentResource($comment->load('user'));
    }


    public function destroy(Request $request, Comment $comment)
    {
        abort_unless($request->user()->id === $comment->user_id || $request->user()->isAdmin(), 403);
        $comment->delete();
        return response()->noContent();
    }
}
