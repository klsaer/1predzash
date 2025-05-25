<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Song;
use App\Models\Tag;
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
        // Eager load all tags with their songs
        $playlist->load(['tags.songs']);
        
        // Get all unique tags from the playlist
        $allTags = $playlist->tags->unique('name');
        
        // Get all songs with their tags
        $songs = $playlist->songs()->with('tags')->get();
        
        // Apply tag filters if present
        if(request()->has('tags') && !empty(request('tags'))) {
            $tags = explode(',', request('tags'));
            $songs = $songs->filter(function($song) use ($tags) {
                return collect($tags)->contains(function($tag) use ($song) {
                    return $song->tags->contains('name', $tag) || 
                           $song->artist === $tag || 
                           $song->album === $tag;
                });
            });
        }
        
        // Get all possible tags (including artists and albums)
        $artists = $songs->pluck('artist')->unique()->filter()->map(function($artist) {
            return new Tag(['name' => $artist, 'type' => 'artist']);
        });
        
        $albums = $songs->pluck('album')->unique()->filter()->map(function($album) {
            return new Tag(['name' => $album, 'type' => 'album']);
        });
        
        $allPossibleTags = $playlist->songs->flatMap->tags
            ->merge($artists)
            ->merge($albums)
            ->unique('name');
        
        return view('playlists.show', [
            'playlist' => $playlist,
            'songs' => $songs,
            'tags' => $allPossibleTags,
            'allTags' => $allTags
        ]);
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

    public function addTag(Request $request, Playlist $playlist, Song $song)
    {
        $request->validate([
            'tag_name' => 'required|string|max:255'
        ]);
    
        $tag = Tag::firstOrCreate([
            'name' => $request->tag_name,
            'user_id' => auth()->id()
        ]);
    
        $song->tags()->syncWithoutDetaching($tag->id);
    
        return back()->with('success', 'Тег добавлен');
    }

    public function removeTag(Playlist $playlist, Song $song, Tag $tag)
    {
        $song->tags()->detach($tag->id);
        return back()->with('success', 'Тег удален');
    }

    public function storeTag(Playlist $playlist, Song $song, Request $request)
    {
        $request->validate([
            'tag_name' => 'required|string|max:255'
        ]);

        $tag = Tag::create([
            'name' => $request->tag_name,
            'user_id' => auth()->id()
        ]);

        $song->tags()->attach($tag->id);

        return back();
    }

    public function index()
    {
        $playlists = Auth::user()->playlists;
        return view('playlists.index', compact('playlists'));
    }
}
