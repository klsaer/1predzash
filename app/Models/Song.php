<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'album',
        'audio_path',
        'cover_path',
        'duration'
    ];

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
