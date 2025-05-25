<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/search', [SongController::class, 'search'])->name('songs.search');

Route::middleware(['auth', 'verified'])->group(function () {
    // Профиль пользователя
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Плейлисты
    Route::resource('playlists', PlaylistController::class);
    Route::post('/playlists/{playlist}/add-song', [PlaylistController::class, 'addSong'])
        ->name('playlists.add-song');
    Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])
        ->name('playlists.show');
    // Теги для песен в плейлистах
    Route::post('/playlists/{playlist}/songs/{song}/tags', [PlaylistController::class, 'storeTag'])
        ->name('playlists.songs.tags.store');
    Route::delete('/playlists/{playlist}/songs/{song}/tags/{tag}', [PlaylistController::class, 'removeTag'])
        ->name('playlists.songs.tags.destroy');

    // Аудио файлы
    Route::get('/audio/{file}', function($file) {
        $path = storage_path("app/audio/$file");
        return response()->file($path, [
            'Content-Type' => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
            'Content-Length' => filesize($path)
        ]);
    });
});

require __DIR__.'/auth.php';
