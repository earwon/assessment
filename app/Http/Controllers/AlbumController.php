<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller {
    public function index(Request $request) {
        $query = Album::query();

        // Sorting
        if ($request->sort === 'votes') {
            $query->orderByDesc('votes')->orderBy('title');
        }

        // Searching
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('artist', 'like', '%' . $request->search . '%');
        }

        // Pagination / Lazy Loading
        return $query->paginate(10);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'release_date' => 'required|date',
        ]);

        $album = Album::create($validated);
        return response()->json($album, 201);
    }

    public function vote(Request $request, $albumId) {
        $user = Auth::user();
        $album = Album::findOrFail($albumId);

        // Check if user already voted
        $existingVote = Vote::where('user_id', $user->id)->where('album_id', $albumId)->first();
        
        if ($existingVote) {
            return response()->json(['message' => 'You have already voted for this album'], 403);
        }

        $voteType = $request->input('type');
        if (!in_array($voteType, ['up', 'down'])) {
            return response()->json(['message' => 'Invalid vote type'], 400);
        }

        // Register the vote
        Vote::create([
            'user_id' => $user->id,
            'album_id' => $albumId,
            'type' => $voteType,
        ]);

        // Update album votes count
        $album->votes += ($voteType === 'up') ? 1 : -1;
        $album->save();

        return response()->json(['message' => 'Vote recorded', 'votes' => $album->votes]);
    }

    public function destroy($id) {
        $album = Album::findOrFail($id);
        
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $album->delete();
        return response()->json(['message' => 'Album deleted']);
    }
}
