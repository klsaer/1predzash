<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function songs()
    {
        return $this->belongsToMany(Song::class);
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_tag', 'tag_id', 'playlist_id');
    }
}