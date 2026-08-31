<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'cover_image_url' => $this->cover_image_url,
            'category' => $this->category,
            'views' => $this->views,
            'published_at' => $this->published_at,
            'likes_count' => $this->likes()->count(),
            'comments_count' => $this->comments()->count(),
            'author_name' => $this->user?->name ?? 'AFCE Editorial',
        ];
    }
}

