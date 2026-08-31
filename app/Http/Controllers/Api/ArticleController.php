<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
{
    $articles = Article::published()
        ->when($request->search, function ($q, $search) {
            $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        })
        ->when($request->category, fn($q, $c) => $q->where('category', $c))
        ->when($request->sort === 'trending', fn($q) => $q->orderByDesc('views'), fn($q) => $q->latest('published_at'))
        ->paginate(15);

    return ArticleResource::collection($articles);
}

    public function show(Article $article)
    {
        abort_unless($article->status === 'published', 404);
        $article->increment('views');
        return new ArticleResource($article);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'cover_image_url' => 'nullable|string',
            'category' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        $data['user_id'] = $request->user()->id;
        if (($data['status'] ?? null) === 'published') {
            $data['published_at'] = now();
        }

        $article = Article::create($data);
        return new ArticleResource($article);
    }

    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'sometimes|string',
            'cover_image_url' => 'nullable|string',
            'category' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        if (($data['status'] ?? null) === 'published' && $article->status !== 'published') {
            $data['published_at'] = now();
        }

        $article->update($data);
        return new ArticleResource($article);
    }

    public function destroy(Request $request, Article $article)
    {
        $this->authorize('delete', $article);
        $article->delete();
        return response()->noContent();
    }

    public function mine(Request $request)
{
    $articles = Article::where('user_id', $request->user()->id)
        ->latest()
        ->paginate(15);

    return ArticleResource::collection($articles);
}
}
