<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function songs()
    {
        return $this->belongsToMany(Song::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'playlist_tag', 'playlist_id', 'tag_id');
    }
}
