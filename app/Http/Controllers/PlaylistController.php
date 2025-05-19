<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistController extends Controller
{
    public function apiIndex()
    {
        return Auth::user()->playlists->map(function($playlist) {
            return [
                'id' => $playlist->id,
                'name' => $playlist->name
            ];
        })->toArray();
    }

    public function show(Playlist $playlist)
    {
        $songs = $playlist->songs;
        return view('playlists.show', compact('playlist', 'songs'));
    }

    public function create()
    {
        return view('playlists.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $playlist = Playlist::create([
            'user_id' => Auth::id(),
            'name' => $request->name
        ]);

        return redirect()->route('playlists.index');
    }

    public function destroy(Playlist $playlist)
    {
        $playlist->delete();
        return redirect()->route('playlists.index');
    }

    public function addSong(Playlist $playlist, Request $request)
    {
        $request->validate(['song_id' => 'required|exists:songs,id']);
        
        $playlist->songs()->attach($request->song_id);
        
        return response()->json(['success' => true]);
    }

    public function index()
    {
        $playlists = Auth::user()->playlists;
        return view('playlists.index', compact('playlists'));
    }
}
