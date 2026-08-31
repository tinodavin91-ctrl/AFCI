<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\TrendingController;
use App\Http\Controllers\Api\CategoryController;

use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public content & trends browsing
Route::get('/trending', [TrendingController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'categories']);
Route::get('/genres', [CategoryController::class, 'genres']);

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

    // Likes
    Route::post('/{type}/{id}/like', [LikeController::class, 'toggle'])
        ->where('type', 'video|track|article');

    // Comments
    Route::post('/{type}/{id}/comments', [CommentController::class, 'store'])
        ->where('type', 'video|track|article');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});
