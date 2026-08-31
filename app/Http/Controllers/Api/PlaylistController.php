<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id;

        $playlists = Playlist::with('user')
            ->where('is_public', true)
            ->when($userId, fn ($q) => $q->orWhere('user_id', $userId))
            ->latest()
            ->paginate(15);

        return response()->json($playlists);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;

        $playlist = Playlist::create($validated);

        return response()->json($playlist->load('user'), 201);
    }

    public function show(Playlist $playlist)
    {
        abort_unless($playlist->is_public || auth()->id() === $playlist->user_id, 403);

        return response()->json($playlist->load(['user', 'tracks']));
    }

    public function update(Request $request, Playlist $playlist)
    {
        abort_unless($request->user()->id === $playlist->user_id || $request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string',
            'is_public' => 'sometimes|boolean',
        ]);

        $playlist->update($validated);

        return response()->json($playlist);
    }

    public function destroy(Request $request, Playlist $playlist)
    {
        abort_unless($request->user()->id === $playlist->user_id || $request->user()->isAdmin(), 403);

        $playlist->delete();

        return response()->noContent();
    }

    public function addTrack(Request $request, Playlist $playlist)
    {
        abort_unless($request->user()->id === $playlist->user_id, 403);

        $validated = $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        $trackId = $validated['track_id'];

        if (! $playlist->tracks()->where('track_id', $trackId)->exists()) {
            $nextPosition = $playlist->tracks()->max('playlist_track.position') + 1;
            $playlist->tracks()->attach($trackId, ['position' => $nextPosition]);
        }

        return response()->json([
            'message' => 'Track added to playlist',
            'playlist' => $playlist->load(['user', 'tracks']),
        ]);
    }

    public function removeTrack(Request $request, Playlist $playlist, int $trackId)
    {
        abort_unless($request->user()->id === $playlist->user_id, 403);

        $playlist->tracks()->detach($trackId);

        return response()->json([
            'message' => 'Track removed from playlist',
            'playlist' => $playlist->load(['user', 'tracks']),
        ]);
    }
}
