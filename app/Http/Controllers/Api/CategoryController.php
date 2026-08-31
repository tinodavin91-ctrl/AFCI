<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Track;
use App\Models\Video;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function categories()
    {
        $videoCategories = Video::published()->whereNotNull('category')->distinct()->pluck('category');
        $articleCategories = Article::published()->whereNotNull('category')->distinct()->pluck('category');

        $categories = $videoCategories->concat($articleCategories)->unique()->values();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function genres()
    {
        $genres = Track::published()->whereNotNull('genre')->distinct()->pluck('genre')->values();

        return response()->json([
            'genres' => $genres,
        ]);
    }
}
