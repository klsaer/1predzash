@extends('layouts.app')

@section('content')
<div class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar p-6">
        <div class="flex justify-between items-center mb-8">
            <a href="{{ url('/dashboard') }}" class="text-gray-400 hover:text-white mr-4">← На главную</a>
            <h1 class="text-3xl font-bold text-white">{{ $playlist->name }}</h1>
            <form action="{{ route('playlists.destroy', $playlist) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-400">Удалить</button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-1 p-8 overflow-y-auto">
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-6 text-white">{{ $playlist->name }}</h2>
            
            @if($songs->count() > 0)
    <div class="grid grid-cols-1 gap-4">
        @foreach($songs as $song)
            @php
                $shouldShow = true;
                if(request()->has('tags') && !empty(request('tags'))) {
                    $selectedTags = explode(',', request('tags'));
                    $shouldShow = $song->tags->pluck('name')->intersect($selectedTags)->count() > 0 ||
                                 in_array($song->artist, $selectedTags) ||
                                 in_array($song->album, $selectedTags);
                }
            @endphp
            <div class="bg-gray-800 p-4 rounded-lg flex items-center {{ $shouldShow ? '' : 'hidden' }}">
                @if($song->cover_path)
                    <img src="{{ asset($song->cover_path) }}" 
                         alt="Album Cover" 
                         class="w-16 h-16 rounded mr-4 cursor-pointer"
                         onclick="playSong('{{ asset($song->audio_path) }}', '{{ $song->title }}', '{{ $song->artist ?? 'Unknown' }}', '{{ asset($song->cover_path) }}')">
                @else
                    <div class="w-16 h-16 rounded mr-4 bg-gray-700 cursor-pointer"
                         onclick="playSong('{{ asset($song->audio_path) }}', '{{ $song->title }}', '{{ $song->artist ?? 'Unknown' }}', null)"></div>
                @endif
                <div>
                    <h3 class="text-lg font-semibold">{{ $song->title }}</h3>
                    <p class="text-gray-400">{{ $song->artist ?? 'Unknown' }}</p>
                    <div class="flex flex-wrap mt-2">
                        @foreach($playlist->tags as $tag)
                            <span class="bg-gray-700 text-xs px-2 py-1 rounded mr-1 mb-1 flex items-center">
                                {{ $tag->name }}
                                <form method="POST" action="{{ route('playlists.songs.tags.destroy', ['playlist' => $playlist, 'song' => $song, 'tag' => $tag]) }}" class="ml-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs">×</button>
                                </form>
                            </span>
                        @endforeach
                        <form method="POST" action="{{ route('playlists.songs.tags.store', ['playlist' => $playlist, 'song' => $song]) }}" class="ml-2">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            <input type="text" name="tag_name" placeholder="Добавить тег" class="bg-gray-700 text-white text-xs px-2 py-1 rounded w-24">
                        </form>
                        @foreach($song->tags as $tag)
                            <span class="bg-gray-700 text-xs px-2 py-1 rounded mr-1 mb-1 flex items-center">
                                {{ $tag->name }}
                                <form method="POST" action="{{ route('playlists.songs.tags.destroy', ['playlist' => $playlist, 'song' => $song, 'tag' => $tag]) }}" class="ml-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs">×</button>
                                </form>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
        </div>
    </div>
</div>
<div class="mb-4">
    <h3 class="text-lg font-semibold mb-2">Фильтр по тегам</h3>
    <div class="flex flex-wrap gap-2">
        @if(request()->has('tags') && !empty(request('tags')))
            <a href="{{ route('playlists.show', ['playlist' => $playlist]) }}" 
               class="px-3 py-1 rounded-full text-sm bg-red-600 hover:bg-red-500">
                Очистить теги
            </a>
        @endif
        @foreach($tags as $tag)
            @php
                $currentTags = request('tags') ? explode(',', request('tags')) : [];
                $isActive = in_array($tag->name, $currentTags);
                $newTags = $isActive 
                    ? array_diff($currentTags, [$tag->name])
                    : array_merge($currentTags, [$tag->name]);
            @endphp
            <a href="{{ route('playlists.show', ['playlist' => $playlist, 'tags' => implode(',', $newTags)]) }}"
               class="px-3 py-1 rounded-full text-sm 
                      {{ $isActive ? 'bg-purple-600' : '' }}
                      {{ $tag->type == 'artist' ? 'bg-blue-600' : ($tag->type == 'album' ? 'bg-green-600' : 'bg-gray-700') }}">
                {{ $tag->name }}
            </a>
        @endforeach
    </div>
</div>

<!-- Player Controls -->
<div id="player-controls">
    <style>
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
        
        // Fix: Pass the event parameter to the function
        const event = window.event || arguments.callee.caller.arguments[0];
        event.currentTarget.classList.add('playing');
        
        try {
            const response = await fetch(songUrl);
            const blob = await response.blob();
            const blobUrl = URL.createObjectURL(blob);
            
            audio = new Audio(blobUrl);
            
            // Add ended event listener
            audio.addEventListener('ended', playNextSong);
            function playNextSong() {
                const currentSongElement = document.querySelector('.playing');
                if (!currentSongElement) return;
                
                // Find parent song container - using multiple possible selectors
                const songContainer = currentSongElement.closest('.bg-gray-800, [data-song-id]');
                if (!songContainer) return;
                
                // Find next song container
                const nextSongContainer = songContainer.nextElementSibling;
                if (!nextSongContainer) return;
                
                // Find playable element (either image or placeholder div)
                const playElement = nextSongContainer.querySelector('img[onclick^="playSong"], div[onclick^="playSong"]');
                if (playElement) playElement.click();
            }

            // Also update your playSong function to store current song index
            async function playSong(songUrl, songTitle, songArtist, songCover) {
                // ... existing code ...
                
                // Store current song element
                document.querySelectorAll('.playing').forEach(el => el.classList.remove('playing'));
                event.currentTarget.classList.add('playing');
                
                // ... rest of existing code ...
            }

            
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
                cover: songCover || 'https://via.placeholder.com/50'
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
@endsection


