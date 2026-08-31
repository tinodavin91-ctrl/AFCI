<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
   public function index(Request $request)
{
    $videos = Video::published()
        ->when($request->search, function ($q, $search) {
            $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        })
        ->when($request->category, fn($q, $c) => $q->where('category', $c))
        ->when($request->sort === 'trending', fn($q) => $q->orderByDesc('views'), fn($q) => $q->latest('published_at'))
        ->paginate(15);

    return VideoResource::collection($videos);
}

    public function show(Video $video)
    {
        abort_unless($video->status === 'published', 404);
        $video->increment('views');
        return new VideoResource($video);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|string',
            'thumbnail_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'category' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        $data['user_id'] = $request->user()->id;
        if (($data['status'] ?? null) === 'published') {
            $data['published_at'] = now();
        }

        $video = Video::create($data);
        return new VideoResource($video);
    }

    public function update(Request $request, Video $video)
    {
        $this->authorize('update', $video);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'sometimes|string',
            'thumbnail_url' => 'nullable|string',
            'duration' => 'nullable|integer',
            'category' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        if (($data['status'] ?? null) === 'published' && $video->status !== 'published') {
            $data['published_at'] = now();
        }

        $video->update($data);
        return new VideoResource($video);
    }

    public function destroy(Request $request, Video $video)
    {
        $this->authorize('delete', $video);
        $video->delete();
        return response()->noContent();
    }

    public function mine(Request $request)
{
    $videos = Video::where('user_id', $request->user()->id)
        ->latest()
        ->paginate(15);

    return VideoResource::collection($videos);
}
}
