<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrackResource;
use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
   public function index(Request $request)
{
    $tracks = Track::published()
        ->when($request->search, function ($q, $search) {
            $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
                    ->orWhere('album', 'like', "%{$search}%");
            });
        })
        ->when($request->genre, fn($q, $g) => $q->where('genre', $g))
        ->when($request->sort === 'trending', fn($q) => $q->orderByDesc('plays'), fn($q) => $q->latest('published_at'))
        ->paginate(15);

    return TrackResource::collection($tracks);
}

    public function show(Track $track)
    {
        abort_unless($track->status === 'published', 404);
        $track->increment('plays');
        return new TrackResource($track);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'album' => 'nullable|string|max:255',
            'audio_url' => 'required|string',
            'cover_art_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'genre' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        $data['user_id'] = $request->user()->id;
        if (($data['status'] ?? null) === 'published') {
            $data['published_at'] = now();
        }

        $track = Track::create($data);
        return new TrackResource($track);
    }

    public function update(Request $request, Track $track)
    {
        $this->authorize('update', $track);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'artist' => 'sometimes|string|max:255',
            'album' => 'nullable|string|max:255',
            'audio_url' => 'sometimes|string',
            'cover_art_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'genre' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        if (($data['status'] ?? null) === 'published' && $track->status !== 'published') {
            $data['published_at'] = now();
        }

        $track->update($data);
        return new TrackResource($track);
    }

    public function destroy(Request $request, Track $track)
    {
        $this->authorize('delete', $track);
        $track->delete();
        return response()->noContent();
    }

    public function mine(Request $request)
{
    $tracks = Track::where('user_id', $request->user()->id)
        ->latest()
        ->paginate(15);

    return TrackResource::collection($tracks);
}
}
