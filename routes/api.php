<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaylistController;

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/playlists', [PlaylistController::class, 'apiIndex'])->middleware('auth:sanctum');
    Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'addSong']);
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
