<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Song;

class SongsTableSeeder extends Seeder
{
    public function run()
    {
        $songs = [
            [
                'title' => 'Bohemian Rhapsody', 
                'artist' => 'Queen',
                'album' => 'A Night at the Opera',
                'year' => 1975,
                'cover_path' => 'img/covers/1.jpg',
                'audio_path' => 'audio/1.mp3' // Добавляем путь к аудио
            ],
            ['title' => 'Hotel California', 'artist' => 'Eagles', 'album' => 'Hotel California', 'year' => 1976, 'cover_path' => 'img/covers/2.jpg', 'audio_path' => 'audio/2.mp3'],
            ['title' => 'Imagine', 'artist' => 'John Lennon', 'album' => 'Imagine', 'year' => 1971, 'cover_path' => 'img/covers/3.jpg', 'audio_path' => 'audio/3.mp3'],
            ['title' => 'Smells Like Teen Spirit', 'artist' => 'Nirvana', 'album' => 'Nevermind', 'year' => 1991, 'cover_path' => 'img/covers/4.jpg', 'audio_path' => 'audio/4.mp3'],
            ['title' => 'Billie Jean', 'artist' => 'Michael Jackson', 'album' => 'Thriller', 'year' => 1982, 'cover_path' => 'img/covers/5.jpg', 'audio_path' => 'audio/5.mp3'],
            ['title' => 'Like a Rolling Stone', 'artist' => 'Bob Dylan', 'album' => 'Highway 61 Revisited', 'year' => 1965, 'cover_path' => 'img/covers/6.jpg', 'audio_path' => 'audio/6.mp3'],
            ['title' => 'Sweet Child O\'Mine', 'artist' => 'Guns N\' Roses', 'album' => 'Appetite for Destruction', 'year' => 1987, 'cover_path' => 'img/covers/7.jpg', 'audio_path' => 'audio/7.mp3'],
            ['title' => 'Stairway to Heaven', 'artist' => 'Led Zeppelin', 'album' => 'Led Zeppelin IV', 'year' => 1971, 'cover_path' => 'img/covers/8.jpg', 'audio_path' => 'audio/8.mp3'],
            ['title' => 'Yesterday', 'artist' => 'The Beatles', 'album' => 'Help!', 'year' => 1965, 'cover_path' => 'img/covers/9.jpg', 'audio_path' => 'audio/9.mp3'],
            ['title' => 'Purple Haze', 'artist' => 'Jimi Hendrix', 'album' => 'Are You Experienced', 'year' => 1967, 'cover_path' => 'img/covers/10.jpg', 'audio_path' => 'audio/10.mp3'],
        ];

        foreach ($songs as $song) {
            Song::create($song);
        }
    }
}
