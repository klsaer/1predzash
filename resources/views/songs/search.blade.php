@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white mr-4">
            ← Назад
        </a>
        <h1 class="text-2xl font-bold">Поиск песен</h1>
    </div>
    
    <form action="{{ route('songs.search') }}" method="GET" class="mb-6">
        <div class="flex">
            <input type="text" name="q" value="{{ $query ?? '' }}" 
                   class="px-4 py-2 border rounded-l-lg w-full bg-gray-700 text-white" 
                   placeholder="Введите название песни">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-lg">
                Поиск
            </button>
        </div>
    </form>
    
    @if(isset($songs) && $songs->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($songs as $song)
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
        
        <div class="mt-6">
            {{ $songs->links() }}
        </div>
    @elseif(isset($query))
        <p class="text-gray-600">Ничего не найдено по запросу "{{ $query }}"</p>
    @endif
</div>

<!-- Player Controls -->
<div id="player-controls" class="fixed bottom-0 left-0 right-0 bg-gray-800 p-4 border-t border-gray-700">
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
</div>

<!-- Playlist Modal -->
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

<script>
    // Player and playlist modal scripts from dashboard
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
                coverElement.innerHTML = `<img src="${songCover}" alt="Cover" class="w-12 h-12 rounded">`;
                coverElement.style.backgroundColor = 'transparent';
            } else {
                coverElement.innerHTML = '';
                coverElement.style.backgroundColor = '#383838';
            }
            
            audio.addEventListener('play', function() {
                document.getElementById('now-playing').textContent = songTitle;
                document.getElementById('now-playing-artist').textContent = songArtist;
            });
            
            audio.addEventListener('timeupdate', function() {
                const progress = document.getElementById('progress');
                progress.value = (audio.currentTime / audio.duration) * 100;
                document.getElementById('current-time').textContent = formatTime(audio.currentTime);
            });
            
            audio.addEventListener('loadedmetadata', function() {
                document.getElementById('duration').textContent = formatTime(audio.duration);
            });
            
            audio.play();
            
            document.getElementById('play-icon').classList.add('hidden');
            document.getElementById('pause-icon').classList.remove('hidden');
            
            document.getElementById('play-pause').addEventListener('click', function(e) {
                e.stopPropagation();
                if (audio.paused) {
                    audio.play();
                    document.getElementById('play-icon').classList.add('hidden');
                    document.getElementById('pause-icon').classList.remove('hidden');
                } else {
                    audio.pause();
                    document.getElementById('play-icon').classList.remove('hidden');
                    document.getElementById('pause-icon').classList.add('hidden');
                }
            });
            
            document.getElementById('progress').addEventListener('input', function() {
                audio.currentTime = (this.value / 100) * audio.duration;
            });
            
            document.getElementById('volume').addEventListener('input', function() {
                audio.volume = this.value;
                document.getElementById('volume-value').textContent = `${Math.round(this.value * 100)}%`;
            });
            
            currentSong = { url: songUrl, title: songTitle, artist: songArtist };
        } catch (error) {
            console.error('Error playing song:', error);
        }
    }
    
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        seconds = Math.floor(seconds % 60);
        return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }
    
    function showPlaylistModal(songId) {
        fetch(`/playlists/for-song/${songId}`)
            .then(response => response.json())
            .then(playlists => {
                const container = document.getElementById('playlists-container');
                container.innerHTML = '';
                
                playlists.forEach(playlist => {
                    const playlistElement = document.createElement('div');
                    playlistElement.className = 'p-2 hover:bg-gray-700 rounded cursor-pointer';
                    playlistElement.textContent = playlist.name;
                    playlistElement.onclick = () => {
                        fetch(`/playlists/${playlist.id}/add-song/${songId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(() => hidePlaylistModal());
                    };
                    container.appendChild(playlistElement);
                });
                
                document.getElementById('playlist-modal').classList.remove('hidden');
            });
    }
    
    function hidePlaylistModal() {
        document.getElementById('playlist-modal').classList.add('hidden');
    }
</script>
@endsection

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