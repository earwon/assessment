<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller {
    public function index(Request $request) {
        // Get sorting parameters
        $sortBy = $request->query('sort', 'votes'); // Default: Sort by votes
        $order = $sortBy === 'title' ? 'asc' : 'desc'; // Alphabetical if title

        // Fetch albums with vote count & user's vote status
        $albums = Album::withCount('votes')
            ->when($sortBy === 'votes', function ($query) {
                $query->orderBy('votes_count', 'desc')->orderBy('title', 'asc'); // Sort by votes then title
            })
            ->when($sortBy === 'title', function ($query) {
                $query->orderBy('title', 'asc');
            })
            ->paginate(20); // Use pagination

        if(Auth::check()) {
            $userId = Auth::id();
            foreach ($albums as $album) {
                $album->userVote = $album->votes()->where('user_id', $userId)->value('vote');
            }
        }

        return response()->json([
            'albums' => $albums,
        ]);
    }

    public function vote(Request $request, $albumId) {
        $user = Auth::user();
        $album = Album::findOrFail($albumId);
    
        $request->validate([
            'vote' => 'required|in:upvote,downvote',
        ]);
    
        $newVoteType = $request->vote;
    
        // Check if the user has already voted
        $existingVote = Vote::where('user_id', $user->id)
                            ->where('album_id', $albumId)
                            ->first();
    
        if ($existingVote) {
            if ($existingVote->vote === $newVoteType) {
                // If user clicks the same vote, remove their vote (toggle off)
                $existingVote->delete();
                return response()->json(['message' => 'Vote removed'], 200);
            } else {
                // If user changes vote, update it
                $existingVote->update(['vote' => $newVoteType]);
                return response()->json(['message' => 'Vote updated'], 200);
            }
        } else {
            // If no existing vote, create a new one
            Vote::create([
                'user_id' => $user->id,
                'album_id' => $albumId,
                'vote' => $newVoteType,
            ]);
            return response()->json(['message' => 'Vote added'], 201);
        }
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
