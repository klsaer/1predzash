<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SongController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        $songs = \App\Models\Song::when($query, function($q) use ($query) {
            return $q->where('title', 'like', '%'.$query.'%');
        })->paginate(10);
        
        return view('songs.search', compact('songs', 'query'));
    }
}