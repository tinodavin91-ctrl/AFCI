<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Video extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'video_url', 'thumbnail_url',
        'duration', 'category', 'views', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

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
