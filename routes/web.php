<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PlaylistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Добавляем маршруты для плейлистов
    Route::middleware(['auth', 'verified'])->group(function() {
        Route::resource('playlists', PlaylistController::class);
    });
    Route::post('/playlists/{playlist}/add-song', [PlaylistController::class, 'addSong'])
        ->name('playlists.add-song');
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

Route::middleware(['auth', 'verified'])->group(function() {
    Route::resource('playlists', PlaylistController::class);
});
