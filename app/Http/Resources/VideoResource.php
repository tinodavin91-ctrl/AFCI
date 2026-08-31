<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
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
            'description' => $this->description,
            'video_url' => $this->video_url,
            'thumbnail_url' => $this->thumbnail_url,
            'duration' => $this->duration,
            'category' => $this->category,
            'views' => $this->views,
            'published_at' => $this->published_at,
            'likes_count' => $this->likes()->count(),
            'comments_count' => $this->comments()->count(),
            'author_name' => $this->user?->name ?? 'AFCE Media',
        ];
    }
}

