<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Music App</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Montserrat', sans-serif;
                background-color: #121212;
                color: #ffffff;
            }
            .sidebar {
                background-color: #000000;
                width: 232px;
            }
            .main-content {
                background: linear-gradient(#1A1A1A, #121212);
            }
            .card {
                background: #282828;
                transition: all 0.3s ease;
            }
            .card:hover {
                background: #383838;
                transform: scale(1.03);
            }
            .card img {
                width: 160px;
                height: 160px;
                object-fit: cover;
            }
        </style>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex h-screen">
            <!-- Sidebar -->
            <div class="sidebar p-6">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-white">Music App</h1>
                </div>
                <nav>
                    <ul class="space-y-4">
                        <li><a href="{{ url('/') }}" class="text-white font-semibold">Главная</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="text-white font-semibold">Профиль</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-gray-400 hover:text-white">Выход</button>
                            </form>
                        </li>
                        <li><a href="{{ route('songs.search') }}" class="text-gray-400 hover:text-white">Поиск</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Моя медиатека</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="main-content flex-1 p-8 overflow-y-auto">
                <div class="mb-4 flex space-x-4">
                    <a href="{{ route('playlists.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                        Мои плейлисты
                    </a>
                    <a href="{{ route('playlists.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Создать плейлист
                    </a>
                </div>
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-6 text-white">Популярные треки</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                        @foreach(App\Models\Song::all() as $song)
                        <div class="card p-4 rounded-lg relative group" onclick="playSong('{{ asset($song->audio_path) }}', '{{ $song->title }}', '{{ $song->artist }}', '{{ $song->cover_path ? asset($song->cover_path) : '' }}')">
                            <div class="relative mb-4">
                                @if($song->cover_path)
                                <img src="{{ asset($song->cover_path) }}" alt="Album Cover" class="w-full rounded shadow-lg">
                                @else
                                <img src="https://via.placeholder.com/160" alt="Album Cover" class="w-full rounded shadow-lg">
                                @endif
                            </div>
                            <h3 class="font-semibold text-white truncate">{{ $song->title }}</h3>
                            <p class="text-gray-400 text-sm mt-1 truncate">{{ $song->artist }}</p>
                            @if($song->album)
                            <p class="text-gray-500 text-xs mt-1 truncate">{{ $song->album }}</p>
                            @endif
                            <div class="mt-2">
                                <button onclick="event.stopPropagation(); showPlaylistModal('{{ $song->id }}')" class="text-sm text-gray-400 hover:text-white">
                                    Добавить в плейлист
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <!-- Player bar and content from welcome.blade.php -->
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Player Controls -->
<div id="player-controls">
    <div class="flex items-center justify-between w-full">
        <div class="flex items-center space-x-3">
            <div id="now-playing-cover" class="flex-shrink-0"></div>
            <div class="min-w-0">
                <div id="now-playing" class="text-white font-medium truncate">Не воспроизводится</div>
                <div id="now-playing-artist" class="text-gray-400 text-sm truncate"></div>
            </div>
        </div>
        
        <div class="flex items-center space-x-6">
            <button id="prev-btn" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button id="play-pause">
                <svg id="play-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                </svg>
                <svg id="pause-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
            <button id="next-btn" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div class="flex items-center space-x-3 w-32">
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 010-7.072m0 0a5 5 0 107.072 0"/>
    </svg>
    <input id="volume" type="range" min="0" max="1" step="0.01" value="0.7" class="flex-1">
    <span id="volume-value" class="text-xs text-gray-400 w-8">70%</span>
</div>
        </div>
    </div>
    
    <div class="mt-3 flex items-center justify-center space-x-3 w-full">
        <span id="current-time" class="text-xs text-gray-400 w-10 text-right">0:00</span>
        <input id="progress" type="range" min="0" max="100" value="0" class="flex-1">
        <span id="duration" class="text-xs text-gray-400 w-10">0:00</span>
    </div>

    <div id="playlist-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Добавить в плейлист</h3>
            <div id="playlists-container" class="max-h-60 overflow-y-auto">
                <!-- Плейлисты будут загружены здесь -->
            </div>
            <div class="mt-4 flex justify-end space-x-3">
                <button onclick="hidePlaylistModal()" class="px-4 py-2 rounded bg-gray-600 hover:bg-gray-700">Отмена</button>
            </div>
        </div>
    </div>

</div>

<script>
    let audio = null;
    let currentSong = null;
    
    async function playSong(songUrl, songTitle, songArtist, songCover) {
        if (audio) {
            audio.pause();
            URL.revokeObjectURL(audio.src);
        }
        
        document.querySelectorAll('.playing').forEach(el => {
            el.classList.remove('playing');
        });
        
        event.currentTarget.classList.add('playing');
        
        try {
            const response = await fetch(songUrl);
            const blob = await response.blob();
            const blobUrl = URL.createObjectURL(blob);
            
            audio = new Audio(blobUrl);
            
            // Update cover image
            const coverElement = document.getElementById('now-playing-cover');
            if (songCover) {
                coverElement.innerHTML = `<img src="${songCover}" alt="Cover">`;
                coverElement.style.backgroundColor = 'transparent';
            } else {
                coverElement.innerHTML = '';
                coverElement.style.backgroundColor = '#383838';
            }
            audio.addEventListener('play', function() {
                document.getElementById('now-playing').textContent = `${songTitle} - ${songArtist}`;
            });
            
            audio.addEventListener('error', function() {
                console.error('Audio error:', audio.error);
                alert('Ошибка воспроизведения трека');
            });
            
            audio.play();
            currentSong = {
                url: blobUrl, 
                title: songTitle, 
                artist: songArtist, 
                cover: '{{ $song->cover_path ? asset($song->cover_path) : "https://via.placeholder.com/50" }}'
            };
            
            // Update player UI
            document.getElementById('now-playing-cover').src = currentSong.cover;
            document.getElementById('now-playing').textContent = currentSong.title;
            document.getElementById('now-playing-artist').textContent = currentSong.artist;
            document.getElementById('play-icon').classList.add('hidden');
            document.getElementById('pause-icon').classList.remove('hidden');
            
            // Setup progress tracking
            audio.addEventListener('timeupdate', updateProgress);
            audio.addEventListener('loadedmetadata', function() {
                document.getElementById('duration').textContent = formatTime(audio.duration);
            });
            
        } catch (error) {
            console.error('Error loading audio:', error);
            alert('Ошибка загрузки трека');
        }
    }
    
    function updateProgress() {
        if (!audio) return;
        
        const progress = (audio.currentTime / audio.duration) * 100;
        document.getElementById('progress').value = progress;
        document.getElementById('current-time').textContent = formatTime(audio.currentTime);
    }
    
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }
    
    // Play/Pause button
    document.getElementById('play-pause').addEventListener('click', function(e) {
        e.stopPropagation();
        if (!audio) return;
        
        if (!audio.paused) {
            audio.pause();
            document.getElementById('play-icon').classList.remove('hidden');
            document.getElementById('pause-icon').classList.add('hidden');
        } else {
            audio.play();
            document.getElementById('play-icon').classList.add('hidden');
            document.getElementById('pause-icon').classList.remove('hidden');
        }
    });
    
    // Volume control
    document.getElementById('volume').addEventListener('input', function(e) {
        e.stopPropagation();
        if (audio) {
            audio.volume = this.value;
        }
    });
    
    // Progress seek handler
    document.getElementById('progress').addEventListener('input', function(e) {
        e.stopPropagation();
        if (!audio) return;
        
        const seekTime = (audio.duration * this.value) / 100;
        audio.currentTime = seekTime;
    });
    
    // Next/Prev buttons (placeholder functionality)
    document.getElementById('next-btn').addEventListener('click', function() {
        // Implement next song logic here
    });
    
    document.getElementById('prev-btn').addEventListener('click', function() {
        // Implement previous song logic here
    });
</script>
<style>
    #playlist-modal {
        z-index: 1000;
    }
    
    #playlists-container::-webkit-scrollbar {
        width: 6px;
    }
    
    #playlists-container::-webkit-scrollbar-thumb {
        background: #4b5563;
        border-radius: 3px;
    }
    .playing {
        background: #383838 !important;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
    }
    
    #now-playing-cover {
        width: 64px;
        height: 64px;
        min-width: 64px;
        background-color: #383838;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    #now-playing-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    #player-controls {
        width: 100%;
        max-width: 100%;
        padding: 1rem;
    }
    
    .player-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    #volume {
        -webkit-appearance: none;
        height: 4px;
        background: #535353;
        border-radius: 2px;
    }
    
    #volume::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 12px;
        height: 12px;
        background: white;
        border-radius: 50%;
        cursor: pointer;
    }



    #player-controls {
        background: rgba(40, 40, 40, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        margin: 0 auto 16px;
        padding: 12px 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        max-width: 800px;
        width: calc(100% - 32px);
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
    }

    #progress {
        -webkit-appearance: none;
        height: 4px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 2px;
    }

    #progress::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 16px;
        height: 16px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    #now-playing-cover {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        background: #383838;
        overflow: hidden;
    }

    #play-pause {
        background: white;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    #volume {
        -webkit-appearance: none;
        height: 4px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 2px;
    }

    #volume::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 12px;
        height: 12px;
        background: white;
        border-radius: 50%;
    }
    
    #current-time, #duration {
        width: 40px;
        text-align: center;
        font-size: 12px;
    }
</style>
<script>
    let currentSongId = null;

    function showPlaylistModal(songId, event) {
        if (event) event.stopPropagation();
        currentSongId = songId;
        const modal = document.getElementById('playlist-modal');
        const container = document.getElementById('playlists-container');
        
        container.innerHTML = '<div class="p-3 text-gray-400">Загрузка...</div>';
        modal.classList.remove('hidden');
        
        fetch('/api/playlists', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('sanctum_token')
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Ошибка: ' + response.status);
            return response.json();
        })
        .then(playlists => {
            container.innerHTML = playlists.map(playlist => `
                <div class="p-3 hover:bg-gray-700 rounded cursor-pointer" 
                     onclick="addToPlaylist(${playlist.id})">
                    ${playlist.name}
                </div>
            `).join('');
        })
        .catch(error => {
            container.innerHTML = `<div class="p-3 text-red-400">${error.message}</div>`;
        });
    }

    function hidePlaylistModal() {
        const modal = document.getElementById('playlist-modal');
        modal.classList.add('hidden');
        modal.addEventListener('transitionend', () => {
            const container = document.getElementById('playlists-container');
            container.innerHTML = '';
        }, { once: true });
    }

    function addToPlaylist(playlistId) {
        fetch('/api/playlists/' + playlistId + '/songs', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Authorization': 'Bearer ' + localStorage.getItem('sanctum_token')
            },
            body: JSON.stringify({ song_id: currentSongId })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(text) });
            }
            return response.json();
        })
        .then(data => {
            alert('Песня добавлена в плейлист');
            hidePlaylistModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка: ' + error.message);
        });
    }
</script>

