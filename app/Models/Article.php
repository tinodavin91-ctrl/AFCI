<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Article extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'body', 'cover_image_url',
        'category', 'views', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($article) {
            $article->slug = $article->slug ?: Str::slug($article->title);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function likes(): MorphMany
{
    return $this->morphMany(Like::class, 'likeable');
}

public function comments(): MorphMany
{
    return $this->morphMany(Comment::class, 'commentable');
}
}
