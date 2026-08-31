<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackResource extends JsonResource
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
            'artist' => $this->artist,
            'album' => $this->album,
            'audio_url' => $this->audio_url,
            'cover_art_url' => $this->cover_art_url,
            'duration' => $this->duration,
            'genre' => $this->genre,
            'plays' => $this->plays,
            'published_at' => $this->published_at,
            'likes_count' => $this->likes()->count(),
            'comments_count' => $this->comments()->count(),
        ];
    }
}

