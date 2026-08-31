<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TrendingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\ProfileController;

use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public content, trends & search browsing
Route::get('/trending', [TrendingController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'categories']);
Route::get('/genres', [CategoryController::class, 'genres']);
Route::get('/search', [SearchController::class, 'search']);

Route::get('/videos', [VideoController::class, 'index']);
Route::get('/videos/{video}', [VideoController::class, 'show']);
Route::get('/tracks', [TrackController::class, 'index']);
Route::get('/tracks/{track}', [TrackController::class, 'show']);
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);

// Public comment listing
Route::get('/{type}/{id}/comments', [CommentController::class, 'index'])
    ->where('type', 'video|track|article');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Profile & Creator Stats
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // File Uploads
    Route::post('/upload', [UploadController::class, 'upload']);

    // Content management
    Route::get('/my-videos', [VideoController::class, 'mine']);
    Route::post('/videos', [VideoController::class, 'store']);
    Route::put('/videos/{video}', [VideoController::class, 'update']);
    Route::delete('/videos/{video}', [VideoController::class, 'destroy']);

    Route::get('/my-tracks', [TrackController::class, 'mine']);
    Route::post('/tracks', [TrackController::class, 'store']);
    Route::put('/tracks/{track}', [TrackController::class, 'update']);
    Route::delete('/tracks/{track}', [TrackController::class, 'destroy']);

    Route::get('/my-articles', [ArticleController::class, 'mine']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);

    // Likes & Bookmarks
    Route::post('/{type}/{id}/like', [LikeController::class, 'toggle'])
        ->where('type', 'video|track|article');
    Route::post('/{type}/{id}/bookmark', [BookmarkController::class, 'toggle'])
        ->where('type', 'video|track|article');
    Route::get('/bookmarks', [BookmarkController::class, 'index']);

    // Comments
    Route::post('/{type}/{id}/comments', [CommentController::class, 'store'])
        ->where('type', 'video|track|article');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});

